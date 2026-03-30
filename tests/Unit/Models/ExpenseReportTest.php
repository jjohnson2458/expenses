<?php

namespace Tests\Unit\Models;

use Tests\TestCase;
use App\Models\Expense;
use App\Models\ExpenseReport;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ExpenseReportTest extends TestCase
{
    use RefreshDatabase;

    private function makeReport(array $attrs = []): ExpenseReport
    {
        if (!isset($attrs['user_id'])) {
            $attrs['user_id'] = User::factory()->create()->id;
        }
        return ExpenseReport::create(array_merge([
            'title' => 'Test Report',
            'description' => 'A test report',
            'status' => 'draft',
            'date_from' => '2026-01-01',
            'date_to' => '2026-01-31',
            'total_amount' => 0,
        ], $attrs));
    }

    public function test_can_create_report(): void
    {
        $report = $this->makeReport(['title' => 'January Expenses']);
        $this->assertDatabaseHas('expense_reports', ['title' => 'January Expenses']);
    }

    public function test_can_update_report(): void
    {
        $report = $this->makeReport();
        $report->update(['title' => 'Updated Title', 'status' => 'submitted']);
        $this->assertDatabaseHas('expense_reports', ['id' => $report->id, 'title' => 'Updated Title', 'status' => 'submitted']);
    }

    public function test_can_delete_report(): void
    {
        $report = $this->makeReport();
        $id = $report->id;
        $report->delete();
        $this->assertDatabaseMissing('expense_reports', ['id' => $id]);
    }

    public function test_update_total_calculates_correctly(): void
    {
        $user = User::factory()->create();
        $report = $this->makeReport(['user_id' => $user->id]);

        Expense::create([
            'user_id' => $user->id, 'report_id' => $report->id,
            'description' => 'A', 'amount' => 50.00, 'type' => 'debit', 'expense_date' => '2026-01-10',
        ]);
        Expense::create([
            'user_id' => $user->id, 'report_id' => $report->id,
            'description' => 'B', 'amount' => 30.00, 'type' => 'debit', 'expense_date' => '2026-01-15',
        ]);

        $report->updateTotal();
        $report->refresh();

        $this->assertEquals(80.00, (float) $report->total_amount);
    }

    public function test_has_many_expenses(): void
    {
        $user = User::factory()->create();
        $report = $this->makeReport(['user_id' => $user->id]);

        Expense::create([
            'user_id' => $user->id, 'report_id' => $report->id,
            'description' => 'Test', 'amount' => 10, 'type' => 'debit', 'expense_date' => '2026-01-10',
        ]);

        $this->assertEquals(1, $report->expenses()->count());
    }
}
