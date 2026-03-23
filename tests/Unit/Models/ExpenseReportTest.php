<?php

namespace Tests\Unit\Models;

use Tests\TestCase;
use App\Models\ExpenseReport;

class ExpenseReportTest extends TestCase
{
    private ExpenseReport $model;

    protected function setUp(): void
    {
        parent::setUp();
        $this->model = new ExpenseReport();
    }

    public function test_can_create_report(): void
    {
        $user = $this->createTestUser();

        $id = $this->model->create([
            'user_id' => $user['id'],
            'title' => 'March Expenses',
            'description' => 'All expenses for March 2026',
            'status' => 'draft',
            'date_from' => '2026-03-01',
            'date_to' => '2026-03-31',
            'total_amount' => 0.00,
        ]);

        $this->assertGreaterThan(0, $id);

        $report = $this->model->find($id);
        $this->assertNotNull($report);
        $this->assertEquals('March Expenses', $report['title']);
        $this->assertEquals('draft', $report['status']);
    }

    public function test_can_update_report(): void
    {
        $report = $this->createTestReport(['title' => 'Old Title']);

        $result = $this->model->update((int) $report['id'], [
            'title' => 'Updated Title',
            'status' => 'submitted',
        ]);
        $this->assertTrue($result);

        $updated = $this->model->find((int) $report['id']);
        $this->assertEquals('Updated Title', $updated['title']);
        $this->assertEquals('submitted', $updated['status']);
    }

    public function test_can_delete_report(): void
    {
        $report = $this->createTestReport();

        $result = $this->model->delete((int) $report['id']);
        $this->assertTrue($result);

        $deleted = $this->model->find((int) $report['id']);
        $this->assertNull($deleted);
    }

    public function test_update_total_calculates_correctly(): void
    {
        $user = $this->createTestUser();
        $category = $this->createTestCategory();
        $report = $this->createTestReport(['user_id' => $user['id']]);

        // Add expenses linked to this report
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
            'amount' => 75.50,
        ]);

        // Recalculate the total
        $this->model->updateTotal((int) $report['id']);

        $updated = $this->model->find((int) $report['id']);
        $this->assertEquals('175.50', $updated['total_amount']);
    }
}
