<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Expense;
use App\Models\ExpenseReport;

class ReportCrudTest extends TestCase
{
    private ExpenseReport $reportModel;
    private Expense $expenseModel;

    protected function setUp(): void
    {
        parent::setUp();
        $this->reportModel = new ExpenseReport();
        $this->expenseModel = new Expense();
        $this->actingAsUser();
    }

    public function test_can_create_report(): void
    {
        $user = $this->createTestUser();

        $id = $this->reportModel->create([
            'user_id' => $user['id'],
            'title' => 'Q1 2026 Expenses',
            'description' => 'All expenses for Q1',
            'status' => 'draft',
            'date_from' => '2026-01-01',
            'date_to' => '2026-03-31',
            'total_amount' => 0.00,
        ]);

        $report = $this->reportModel->find($id);
        $this->assertNotNull($report);
        $this->assertEquals('Q1 2026 Expenses', $report['title']);
        $this->assertEquals('draft', $report['status']);
        $this->assertEquals('0.00', $report['total_amount']);
    }

    public function test_can_add_expense_to_report(): void
    {
        $user = $this->createTestUser();
        $category = $this->createTestCategory();
        $report = $this->createTestReport(['user_id' => $user['id']]);

        // Create an expense without a report
        $expense = $this->createTestExpense([
            'user_id' => $user['id'],
            'category_id' => $category['id'],
            'amount' => 100.00,
        ]);

        // Link it to the report
        $this->expenseModel->update((int) $expense['id'], [
            'report_id' => $report['id'],
        ]);

        // Verify the expense is now linked
        $updated = $this->expenseModel->find((int) $expense['id']);
        $this->assertEquals($report['id'], $updated['report_id']);

        // Verify it shows up in getByReport
        $reportExpenses = $this->expenseModel->getByReport((int) $report['id']);
        $this->assertCount(1, $reportExpenses);
        $this->assertEquals($expense['id'], $reportExpenses[0]['id']);
    }

    public function test_can_remove_expense_from_report(): void
    {
        $user = $this->createTestUser();
        $category = $this->createTestCategory();
        $report = $this->createTestReport(['user_id' => $user['id']]);

        $expense = $this->createTestExpense([
            'user_id' => $user['id'],
            'category_id' => $category['id'],
            'report_id' => $report['id'],
            'amount' => 50.00,
        ]);

        // Remove from report by setting report_id to null
        $this->expenseModel->update((int) $expense['id'], [
            'report_id' => null,
        ]);

        $updated = $this->expenseModel->find((int) $expense['id']);
        $this->assertNull($updated['report_id']);

        $reportExpenses = $this->expenseModel->getByReport((int) $report['id']);
        $this->assertCount(0, $reportExpenses);
    }

    public function test_report_total_recalculates(): void
    {
        $user = $this->createTestUser();
        $category = $this->createTestCategory();
        $report = $this->createTestReport(['user_id' => $user['id']]);

        // Add three expenses
        $this->createTestExpense([
            'user_id' => $user['id'],
            'category_id' => $category['id'],
            'report_id' => $report['id'],
            'amount' => 100.00,
        ]);
        $this->createTestExpense([
            'user_id' => $user['id'],
            'category_id' => $category['id'],
            'report_id' => $report['id'],
            'amount' => 200.00,
        ]);
        $expense3 = $this->createTestExpense([
            'user_id' => $user['id'],
            'category_id' => $category['id'],
            'report_id' => $report['id'],
            'amount' => 300.00,
        ]);

        // Recalculate total
        $this->reportModel->updateTotal((int) $report['id']);
        $updated = $this->reportModel->find((int) $report['id']);
        $this->assertEquals('600.00', $updated['total_amount']);

        // Remove one expense and recalculate
        $this->expenseModel->delete((int) $expense3['id']);
        $this->reportModel->updateTotal((int) $report['id']);

        $updated2 = $this->reportModel->find((int) $report['id']);
        $this->assertEquals('300.00', $updated2['total_amount']);
    }
}
