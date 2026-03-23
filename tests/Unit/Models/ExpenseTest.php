<?php

namespace Tests\Unit\Models;

use Tests\TestCase;
use App\Models\Expense;

class ExpenseTest extends TestCase
{
    private Expense $model;

    protected function setUp(): void
    {
        parent::setUp();
        $this->model = new Expense();
    }

    public function test_can_create_expense(): void
    {
        $user = $this->createTestUser();
        $category = $this->createTestCategory();

        $id = $this->model->create([
            'user_id' => $user['id'],
            'category_id' => $category['id'],
            'type' => 'debit',
            'description' => 'Office supplies',
            'amount' => 45.99,
            'expense_date' => '2026-03-15',
            'vendor' => 'Staples',
        ]);

        $this->assertGreaterThan(0, $id);

        $expense = $this->model->find($id);
        $this->assertNotNull($expense);
        $this->assertEquals('Office supplies', $expense['description']);
        $this->assertEquals('45.99', $expense['amount']);
        $this->assertEquals('debit', $expense['type']);
    }

    public function test_can_create_credit_expense(): void
    {
        $user = $this->createTestUser();
        $category = $this->createTestCategory();

        $id = $this->model->create([
            'user_id' => $user['id'],
            'category_id' => $category['id'],
            'type' => 'credit',
            'description' => 'Refund from vendor',
            'amount' => 20.00,
            'expense_date' => '2026-03-10',
            'vendor' => 'Amazon',
        ]);

        $expense = $this->model->find($id);
        $this->assertEquals('credit', $expense['type']);
        $this->assertEquals('20.00', $expense['amount']);
    }

    public function test_can_find_expense_by_id(): void
    {
        $expense = $this->createTestExpense(['description' => 'Findable expense']);

        $found = $this->model->find((int) $expense['id']);
        $this->assertNotNull($found);
        $this->assertEquals('Findable expense', $found['description']);
    }

    public function test_can_update_expense(): void
    {
        $expense = $this->createTestExpense(['description' => 'Original']);

        $result = $this->model->update((int) $expense['id'], [
            'description' => 'Updated',
            'amount' => 99.99,
        ]);
        $this->assertTrue($result);

        $updated = $this->model->find((int) $expense['id']);
        $this->assertEquals('Updated', $updated['description']);
        $this->assertEquals('99.99', $updated['amount']);
    }

    public function test_can_delete_expense(): void
    {
        $expense = $this->createTestExpense();

        $result = $this->model->delete((int) $expense['id']);
        $this->assertTrue($result);

        $deleted = $this->model->find((int) $expense['id']);
        $this->assertNull($deleted);
    }

    public function test_get_by_date_range(): void
    {
        $user = $this->createTestUser();
        $category = $this->createTestCategory();

        $this->createTestExpense([
            'user_id' => $user['id'],
            'category_id' => $category['id'],
            'expense_date' => '2026-01-15',
            'description' => 'January expense',
        ]);
        $this->createTestExpense([
            'user_id' => $user['id'],
            'category_id' => $category['id'],
            'expense_date' => '2026-02-15',
            'description' => 'February expense',
        ]);
        $this->createTestExpense([
            'user_id' => $user['id'],
            'category_id' => $category['id'],
            'expense_date' => '2026-03-15',
            'description' => 'March expense',
        ]);

        $results = $this->model->getByDateRange('2026-01-01', '2026-02-28');
        $descriptions = array_column($results, 'description');

        $this->assertContains('January expense', $descriptions);
        $this->assertContains('February expense', $descriptions);
        $this->assertNotContains('March expense', $descriptions);
    }

    public function test_get_total_by_category(): void
    {
        $user = $this->createTestUser();
        $cat1 = $this->createTestCategory(['name' => 'Food']);
        $cat2 = $this->createTestCategory(['name' => 'Transport']);

        $this->createTestExpense([
            'user_id' => $user['id'],
            'category_id' => $cat1['id'],
            'amount' => 50.00,
            'expense_date' => '2026-03-01',
        ]);
        $this->createTestExpense([
            'user_id' => $user['id'],
            'category_id' => $cat1['id'],
            'amount' => 30.00,
            'expense_date' => '2026-03-05',
        ]);
        $this->createTestExpense([
            'user_id' => $user['id'],
            'category_id' => $cat2['id'],
            'amount' => 15.00,
            'expense_date' => '2026-03-10',
        ]);

        $totals = $this->model->getTotalByCategory('2026-03-01', '2026-03-31');

        $this->assertNotEmpty($totals);

        // Find the Food category total
        $foodTotal = null;
        $transportTotal = null;
        foreach ($totals as $row) {
            if ($row['category_name'] === 'Food') {
                $foodTotal = $row;
            }
            if ($row['category_name'] === 'Transport') {
                $transportTotal = $row;
            }
        }

        $this->assertNotNull($foodTotal);
        $this->assertEquals('80.00', $foodTotal['total_amount']);
        $this->assertEquals(2, $foodTotal['expense_count']);

        $this->assertNotNull($transportTotal);
        $this->assertEquals('15.00', $transportTotal['total_amount']);
        $this->assertEquals(1, $transportTotal['expense_count']);
    }

    public function test_get_dashboard_stats(): void
    {
        $user = $this->createTestUser();
        $category = $this->createTestCategory();

        // Create expenses in current month
        $thisMonth = date('Y-m-15');
        $this->createTestExpense([
            'user_id' => $user['id'],
            'category_id' => $category['id'],
            'amount' => 100.00,
            'expense_date' => $thisMonth,
            'type' => 'debit',
        ]);
        $this->createTestExpense([
            'user_id' => $user['id'],
            'category_id' => $category['id'],
            'amount' => 50.00,
            'expense_date' => $thisMonth,
            'type' => 'debit',
        ]);

        $stats = $this->model->getDashboardStats();

        $this->assertArrayHasKey('total_this_month', $stats);
        $this->assertArrayHasKey('total_last_month', $stats);
        $this->assertArrayHasKey('total_credits', $stats);
        $this->assertArrayHasKey('total_debits', $stats);
        $this->assertArrayHasKey('count', $stats);
        // At least 2 expenses exist (may be more from other test data in same transaction)
        $this->assertGreaterThanOrEqual(2, $stats['count']);
    }

    public function test_get_recent_expenses(): void
    {
        $user = $this->createTestUser();
        $category = $this->createTestCategory();

        for ($i = 1; $i <= 5; $i++) {
            $this->createTestExpense([
                'user_id' => $user['id'],
                'category_id' => $category['id'],
                'description' => "Recent expense {$i}",
                'expense_date' => "2026-03-0{$i}",
            ]);
        }

        $recent = $this->model->getRecentExpenses(3);

        $this->assertCount(3, $recent);
        // Should have category_name from the JOIN
        $this->assertArrayHasKey('category_name', $recent[0]);
    }
}
