<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Expense;
use App\Models\Category;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ExportTest extends TestCase
{
    use RefreshDatabase;

    private function seedExpenses(): User
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $cat = Category::create(['name' => 'Office', 'user_id' => $user->id, 'is_active' => 1, 'sort_order' => 0]);

        Expense::create([
            'user_id' => $user->id, 'category_id' => $cat->id,
            'description' => 'Printer Paper', 'amount' => 45.50,
            'type' => 'debit', 'expense_date' => '2026-03-10', 'vendor' => 'Staples',
        ]);
        Expense::create([
            'user_id' => $user->id,
            'description' => 'Client Payment', 'amount' => 1500.00,
            'type' => 'credit', 'expense_date' => '2026-03-15',
        ]);

        return $user;
    }

    public function test_csv_export_downloads(): void
    {
        $this->seedExpenses();

        $response = $this->get('/export/csv');
        $response->assertStatus(200);
        $response->assertHeader('content-type', 'text/csv; charset=UTF-8');
        $response->assertDownload();
    }

    public function test_csv_export_contains_data(): void
    {
        $this->seedExpenses();

        $response = $this->get('/export/csv');
        $content = $response->streamedContent();
        $this->assertStringContainsString('Printer Paper', $content);
        $this->assertStringContainsString('Client Payment', $content);
    }

    public function test_quickbooks_iif_export(): void
    {
        $this->seedExpenses();

        $response = $this->get('/export/quickbooks');
        $response->assertStatus(200);
        $response->assertDownload();
        $content = $response->streamedContent();
        $this->assertStringContainsString('!TRNS', $content);
        $this->assertStringContainsString('ENDTRNS', $content);
    }

    public function test_ical_export(): void
    {
        $this->seedExpenses();

        $response = $this->get('/export/calendar');
        $response->assertStatus(200);
        $content = $response->streamedContent();
        $this->assertStringContainsString('BEGIN:VCALENDAR', $content);
        $this->assertStringContainsString('BEGIN:VEVENT', $content);
    }

    public function test_ofx_export(): void
    {
        $this->seedExpenses();

        $response = $this->get('/export/ofx');
        $response->assertStatus(200);
        $content = $response->streamedContent();
        $this->assertStringContainsString('OFXHEADER:100', $content);
        $this->assertStringContainsString('<STMTTRN>', $content);
        $this->assertStringContainsString('Printer Paper', $content);
    }

    public function test_qfx_export(): void
    {
        $this->seedExpenses();

        $response = $this->get('/export/qfx');
        $response->assertStatus(200);
        $response->assertDownload();
    }

    public function test_qbo_export(): void
    {
        $this->seedExpenses();

        $response = $this->get('/export/qbo');
        $response->assertStatus(200);
        $response->assertDownload();
    }

    public function test_ofx_export_debit_has_negative_amount(): void
    {
        $this->seedExpenses();

        $response = $this->get('/export/ofx');
        $content = $response->streamedContent();
        $this->assertStringContainsString('<TRNAMT>-45.50', $content);
    }

    public function test_ofx_export_credit_has_positive_amount(): void
    {
        $this->seedExpenses();

        $response = $this->get('/export/ofx');
        $content = $response->streamedContent();
        $this->assertStringContainsString('<TRNAMT>1500.00', $content);
    }
}
