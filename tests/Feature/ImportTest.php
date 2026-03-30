<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Expense;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;

class ImportTest extends TestCase
{
    use RefreshDatabase;

    private function authUser(): User
    {
        $user = User::factory()->create();
        $this->actingAs($user);
        return $user;
    }

    private function makeOfxContent(array $transactions): string
    {
        $content = "OFXHEADER:100\nDATA:OFXSGML\nVERSION:102\nSECURITY:NONE\nENCODING:USASCII\nCHARSET:1252\nCOMPRESSION:NONE\nOLDFILEUID:NONE\nNEWFILEUID:NONE\n\n";
        $content .= "<OFX>\n<SIGNONMSGSRSV1>\n<SONRS>\n<STATUS>\n<CODE>0\n<SEVERITY>INFO\n</STATUS>\n<DTSERVER>20260330120000\n<LANGUAGE>ENG\n</SONRS>\n</SIGNONMSGSRSV1>\n";
        $content .= "<BANKMSGSRSV1>\n<STMTTRNRS>\n<TRNUID>0\n<STATUS>\n<CODE>0\n<SEVERITY>INFO\n</STATUS>\n<STMTRS>\n<CURDEF>USD\n";
        $content .= "<BANKACCTFROM>\n<BANKID>000000000\n<ACCTID>1234567890\n<ACCTTYPE>CHECKING\n</BANKACCTFROM>\n";
        $content .= "<BANKTRANLIST>\n<DTSTART>20260101120000\n<DTEND>20260330120000\n";

        foreach ($transactions as $txn) {
            $content .= "<STMTTRN>\n";
            $content .= "<TRNTYPE>" . ($txn['type'] ?? 'DEBIT') . "\n";
            $content .= "<DTPOSTED>" . ($txn['date'] ?? '20260315120000') . "\n";
            $content .= "<TRNAMT>" . ($txn['amount'] ?? '-10.00') . "\n";
            $content .= "<FITID>" . ($txn['fitid'] ?? uniqid()) . "\n";
            $content .= "<NAME>" . ($txn['name'] ?? 'Test Transaction') . "\n";
            if (isset($txn['memo'])) {
                $content .= "<MEMO>" . $txn['memo'] . "\n";
            }
            $content .= "</STMTTRN>\n";
        }

        $content .= "</BANKTRANLIST>\n</STMTRS>\n</STMTTRNRS>\n</BANKMSGSRSV1>\n</OFX>\n";
        return $content;
    }

    private function makeOfxFile(string $content, string $ext = 'ofx'): UploadedFile
    {
        $path = tempnam(sys_get_temp_dir(), 'ofx');
        file_put_contents($path, $content);
        return new UploadedFile($path, "test.{$ext}", 'application/x-ofx', null, true);
    }

    public function test_import_page_loads(): void
    {
        $this->authUser();
        $response = $this->get('/import');
        $response->assertStatus(200);
        $response->assertSee('OFX');
    }

    public function test_import_ofx_creates_expenses(): void
    {
        $user = $this->authUser();
        $content = $this->makeOfxContent([
            ['name' => 'WEGMANS', 'amount' => '-45.50', 'fitid' => 'FIT001', 'date' => '20260315120000'],
            ['name' => 'PAYROLL DEPOSIT', 'amount' => '2500.00', 'fitid' => 'FIT002', 'date' => '20260314120000'],
        ]);
        $file = $this->makeOfxFile($content, 'ofx');

        $response = $this->post('/import', ['import_file' => $file]);

        $response->assertRedirect('/import');
        $this->assertDatabaseHas('expenses', ['description' => 'WEGMANS', 'type' => 'debit', 'user_id' => $user->id]);
        $this->assertDatabaseHas('expenses', ['description' => 'PAYROLL DEPOSIT', 'type' => 'credit', 'user_id' => $user->id]);
    }

    public function test_import_ofx_skips_duplicates(): void
    {
        $user = $this->authUser();

        // Create an existing expense with same FITID
        Expense::create([
            'user_id' => $user->id, 'description' => 'WEGMANS', 'amount' => 45.50,
            'type' => 'debit', 'expense_date' => '2026-03-15', 'fitid' => 'DUPLICATE001',
        ]);

        $content = $this->makeOfxContent([
            ['name' => 'WEGMANS', 'amount' => '-45.50', 'fitid' => 'DUPLICATE001'],
            ['name' => 'NEW PURCHASE', 'amount' => '-20.00', 'fitid' => 'NEW001'],
        ]);
        $file = $this->makeOfxFile($content);

        $response = $this->post('/import', ['import_file' => $file]);

        // Should have 2 total (1 existing + 1 new), not 3
        $this->assertEquals(2, Expense::where('user_id', $user->id)->count());
        $this->assertDatabaseHas('expenses', ['fitid' => 'NEW001']);
    }

    public function test_import_qfx_works(): void
    {
        $user = $this->authUser();
        $content = $this->makeOfxContent([
            ['name' => 'QFX TEST', 'amount' => '-10.00', 'fitid' => 'QFX001'],
        ]);
        $file = $this->makeOfxFile($content, 'qfx');

        $response = $this->post('/import', ['import_file' => $file]);
        $this->assertDatabaseHas('expenses', ['description' => 'QFX TEST']);
    }

    public function test_import_qbo_works(): void
    {
        $user = $this->authUser();
        $content = $this->makeOfxContent([
            ['name' => 'QBO TEST', 'amount' => '-25.00', 'fitid' => 'QBO001'],
        ]);
        $file = $this->makeOfxFile($content, 'qbo');

        $response = $this->post('/import', ['import_file' => $file]);
        $this->assertDatabaseHas('expenses', ['description' => 'QBO TEST']);
    }

    public function test_import_csv_works(): void
    {
        $user = $this->authUser();
        $csv = "date,description,amount,type\n2026-03-15,Office Supplies,49.99,debit\n";
        $path = tempnam(sys_get_temp_dir(), 'csv');
        file_put_contents($path, $csv);
        $file = new UploadedFile($path, 'test.csv', 'text/csv', null, true);

        $response = $this->post('/import', ['import_file' => $file]);
        $this->assertDatabaseHas('expenses', ['description' => 'Office Supplies']);
    }

    public function test_import_rejects_unsupported_format(): void
    {
        $this->authUser();
        $path = tempnam(sys_get_temp_dir(), 'bad');
        file_put_contents($path, 'not a real file');
        $file = new UploadedFile($path, 'test.pdf', 'application/pdf', null, true);

        $response = $this->post('/import', ['import_file' => $file]);
        $response->assertRedirect('/import');
    }
}
