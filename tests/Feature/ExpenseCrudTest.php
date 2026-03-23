<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Expense;
use App\Models\ExpenseReport;

class ExpenseCrudTest extends TestCase
{
    private Expense $model;

    protected function setUp(): void
    {
        parent::setUp();
        $this->model = new Expense();
        $this->actingAsUser();
    }

    public function test_can_list_expenses(): void
    {
        $user = $this->createTestUser();
        $category = $this->createTestCategory();

        $this->createTestExpense([
            'user_id' => $user['id'],
            'category_id' => $category['id'],
            'description' => 'Lunch',
        ]);
        $this->createTestExpense([
            'user_id' => $user['id'],
            'category_id' => $category['id'],
            'description' => 'Dinner',
        ]);

        $all = $this->model->all();
        $descriptions = array_column($all, 'description');
        $this->assertContains('Lunch', $descriptions);
        $this->assertContains('Dinner', $descriptions);
    }

    public function test_can_create_debit_expense(): void
    {
        $user = $this->createTestUser();
        $category = $this->createTestCategory();

        $id = $this->model->create([
            'user_id' => $user['id'],
            'category_id' => $category['id'],
            'type' => 'debit',
            'description' => 'Office chair',
            'amount' => 299.99,
            'expense_date' => '2026-03-22',
            'vendor' => 'IKEA',
            'notes' => 'Ergonomic chair',
        ]);

        $expense = $this->model->find($id);
        $this->assertEquals('debit', $expense['type']);
        $this->assertEquals('299.99', $expense['amount']);
        $this->assertEquals('Office chair', $expense['description']);
        $this->assertEquals('IKEA', $expense['vendor']);
    }

    public function test_can_create_credit_expense(): void
    {
        $user = $this->createTestUser();
        $category = $this->createTestCategory();

        $id = $this->model->create([
            'user_id' => $user['id'],
            'category_id' => $category['id'],
            'type' => 'credit',
            'description' => 'Refund for defective item',
            'amount' => 49.99,
            'expense_date' => '2026-03-20',
            'vendor' => 'Amazon',
        ]);

        $expense = $this->model->find($id);
        $this->assertEquals('credit', $expense['type']);
        $this->assertEquals('49.99', $expense['amount']);
    }

    public function test_can_update_expense(): void
    {
        $expense = $this->createTestExpense(['description' => 'Original desc']);

        $this->model->update((int) $expense['id'], [
            'description' => 'Updated desc',
            'amount' => 150.00,
        ]);

        $updated = $this->model->find((int) $expense['id']);
        $this->assertEquals('Updated desc', $updated['description']);
        $this->assertEquals('150.00', $updated['amount']);
    }

    public function test_can_delete_expense(): void
    {
        $expense = $this->createTestExpense();

        $this->assertTrue($this->model->delete((int) $expense['id']));
        $this->assertNull($this->model->find((int) $expense['id']));
    }

    public function test_expense_linked_to_report_updates_total(): void
    {
        $user = $this->createTestUser();
        $category = $this->createTestCategory();
        $report = $this->createTestReport(['user_id' => $user['id']]);

        // Add two expenses to the report
        $this->createTestExpense([
            'user_id' => $user['id'],
            'category_id' => $category['id'],
            'report_id' => $report['id'],
            'amount' => 50.00,
        ]);
        $this->createTestExpense([
            'user_id' => $user['id'],
            'category_id' => $category['id'],
            'report_id' => $report['id'],
            'amount' => 75.00,
        ]);

        // Recalculate the report total
        $reportModel = new ExpenseReport();
        $reportModel->updateTotal((int) $report['id']);

        $updatedReport = $reportModel->find((int) $report['id']);
        $this->assertEquals('125.00', $updatedReport['total_amount']);
    }
}
