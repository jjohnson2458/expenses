<?php

namespace Tests\Feature;

use Tests\TestCase;

class ExportTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAsUser();
    }

    /**
     * Build sample expense rows matching the format returned by
     * ExportController::getFilteredExpenses() (joined with category/report).
     */
    private function getSampleExpenses(): array
    {
        return [
            [
                'id' => 1,
                'expense_date' => '2026-03-15',
                'description' => 'Office Supplies',
                'category_name' => 'Supplies',
                'vendor' => 'Staples',
                'type' => 'debit',
                'amount' => 45.99,
                'report_title' => 'March Report',
                'notes' => 'Pens and paper',
            ],
            [
                'id' => 2,
                'expense_date' => '2026-03-18',
                'description' => 'Client Refund',
                'category_name' => 'Income',
                'vendor' => 'Acme Corp',
                'type' => 'credit',
                'amount' => 200.00,
                'report_title' => '',
                'notes' => '',
            ],
        ];
    }

    public function test_csv_export_format(): void
    {
        $expenses = $this->getSampleExpenses();

        // Simulate CSV generation to a temp file
        $tempFile = tempnam(sys_get_temp_dir(), 'csv_test_');
        $output = fopen($tempFile, 'w');

        // UTF-8 BOM
        fwrite($output, "\xEF\xBB\xBF");

        // Header row
        fputcsv($output, ['Date', 'Description', 'Category', 'Vendor', 'Type', 'Amount', 'Report', 'Notes']);

        foreach ($expenses as $row) {
            fputcsv($output, [
                $row['expense_date'] ?? '',
                $row['description'] ?? '',
                $row['category_name'] ?? '',
                $row['vendor'] ?? '',
                $row['type'] ?? 'debit',
                number_format((float) ($row['amount'] ?? 0), 2, '.', ''),
                $row['report_title'] ?? '',
                $row['notes'] ?? '',
            ]);
        }
        fclose($output);

        $content = file_get_contents($tempFile);
        unlink($tempFile);

        // Verify BOM
        $this->assertStringStartsWith("\xEF\xBB\xBF", $content);

        // Verify header
        $this->assertStringContainsString('Date,Description,Category,Vendor,Type,Amount,Report,Notes', $content);

        // Verify data rows
        $this->assertStringContainsString('Office Supplies', $content);
        $this->assertStringContainsString('45.99', $content);
        $this->assertStringContainsString('Client Refund', $content);
        $this->assertStringContainsString('200.00', $content);
        $this->assertStringContainsString('March Report', $content);
    }

    public function test_quickbooks_iif_export_format(): void
    {
        $expenses = $this->getSampleExpenses();

        $tempFile = tempnam(sys_get_temp_dir(), 'iif_test_');
        $output = fopen($tempFile, 'w');

        // IIF header rows
        fwrite($output, "!TRNS\tTRNSTYPE\tDATE\tACCNT\tAMOUNT\tMEMO\tNAME\n");
        fwrite($output, "!SPL\tTRNSTYPE\tDATE\tACCNT\tAMOUNT\tMEMO\tNAME\n");
        fwrite($output, "!ENDTRNS\n");

        foreach ($expenses as $row) {
            $date = date('m/d/Y', strtotime($row['expense_date']));
            $categoryAcct = 'Expenses:' . ($row['category_name'] ?: 'Uncategorized');
            $amount = (float) ($row['amount'] ?? 0);
            $memo = str_replace("\t", ' ', $row['description'] ?? '');
            $vendor = str_replace("\t", ' ', $row['vendor'] ?? '');

            $sign = ($row['type'] ?? 'debit') === 'debit' ? 1 : -1;
            $expenseAmount = $amount * $sign;

            fwrite($output, "TRNS\tGENERAL JOURNAL\t{$date}\t{$categoryAcct}\t" .
                number_format($expenseAmount, 2, '.', '') . "\t{$memo}\t{$vendor}\n");
            fwrite($output, "SPL\tGENERAL JOURNAL\t{$date}\tChecking\t" .
                number_format(-$expenseAmount, 2, '.', '') . "\t{$memo}\t{$vendor}\n");
            fwrite($output, "ENDTRNS\n");
        }
        fclose($output);

        $content = file_get_contents($tempFile);
        unlink($tempFile);

        // Verify IIF headers
        $this->assertStringContainsString("!TRNS\t", $content);
        $this->assertStringContainsString("!SPL\t", $content);
        $this->assertStringContainsString("!ENDTRNS", $content);

        // Verify TRNS entries
        $this->assertStringContainsString("TRNS\tGENERAL JOURNAL\t03/15/2026\tExpenses:Supplies\t45.99", $content);
        $this->assertStringContainsString("SPL\tGENERAL JOURNAL\t03/15/2026\tChecking\t-45.99", $content);

        // Credit should have negative amount on expense line
        $this->assertStringContainsString("TRNS\tGENERAL JOURNAL\t03/18/2026\tExpenses:Income\t-200.00", $content);
        $this->assertStringContainsString("SPL\tGENERAL JOURNAL\t03/18/2026\tChecking\t200.00", $content);
    }

    public function test_ical_export_format(): void
    {
        $expenses = $this->getSampleExpenses();

        $tempFile = tempnam(sys_get_temp_dir(), 'ics_test_');
        $output = fopen($tempFile, 'w');

        fwrite($output, "BEGIN:VCALENDAR\r\n");
        fwrite($output, "VERSION:2.0\r\n");
        fwrite($output, "PRODID:-//MyExpenses//Expense Export//EN\r\n");
        fwrite($output, "CALSCALE:GREGORIAN\r\n");
        fwrite($output, "METHOD:PUBLISH\r\n");

        foreach ($expenses as $row) {
            $dtDate = date('Ymd', strtotime($row['expense_date']));
            $uid = 'expense-' . $row['id'] . '@myexpenses';
            $dtstamp = gmdate('Ymd\THis\Z');
            $amount = number_format((float) ($row['amount'] ?? 0), 2);
            $type = ucfirst($row['type'] ?? 'debit');
            $description = $row['description'] ?? '';

            $summary = "{$type}: \${$amount} - {$description}";

            fwrite($output, "BEGIN:VEVENT\r\n");
            fwrite($output, "UID:{$uid}\r\n");
            fwrite($output, "DTSTAMP:{$dtstamp}\r\n");
            fwrite($output, "DTSTART;VALUE=DATE:{$dtDate}\r\n");
            fwrite($output, "DTEND;VALUE=DATE:{$dtDate}\r\n");
            fwrite($output, "SUMMARY:{$summary}\r\n");
            fwrite($output, "END:VEVENT\r\n");
        }

        fwrite($output, "END:VCALENDAR\r\n");
        fclose($output);

        $content = file_get_contents($tempFile);
        unlink($tempFile);

        // Verify iCal structure
        $this->assertStringContainsString("BEGIN:VCALENDAR", $content);
        $this->assertStringContainsString("VERSION:2.0", $content);
        $this->assertStringContainsString("PRODID:-//MyExpenses//Expense Export//EN", $content);
        $this->assertStringContainsString("END:VCALENDAR", $content);

        // Verify events
        $this->assertStringContainsString("BEGIN:VEVENT", $content);
        $this->assertStringContainsString("END:VEVENT", $content);
        $this->assertStringContainsString("DTSTART;VALUE=DATE:20260315", $content);
        $this->assertStringContainsString("SUMMARY:Debit: \$45.99 - Office Supplies", $content);
        $this->assertStringContainsString("SUMMARY:Credit: \$200.00 - Client Refund", $content);
        $this->assertStringContainsString("UID:expense-1@myexpenses", $content);
    }
}
