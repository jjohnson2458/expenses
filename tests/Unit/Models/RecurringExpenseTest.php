<?php

namespace Tests\Unit\Models;

use Tests\TestCase;
use App\Models\RecurringExpense;

class RecurringExpenseTest extends TestCase
{
    private RecurringExpense $model;

    protected function setUp(): void
    {
        parent::setUp();
        $this->model = new RecurringExpense();
    }

    public function test_can_create_recurring_expense(): void
    {
        $user = $this->createTestUser();
        $category = $this->createTestCategory();

        $id = $this->model->create([
            'user_id' => $user['id'],
            'category_id' => $category['id'],
            'type' => 'debit',
            'description' => 'Monthly rent',
            'amount' => 1200.00,
            'vendor' => 'Landlord',
            'day_of_month' => 1,
            'is_active' => 1,
        ]);

        $this->assertGreaterThan(0, $id);

        $recurring = $this->model->find($id);
        $this->assertNotNull($recurring);
        $this->assertEquals('Monthly rent', $recurring['description']);
        $this->assertEquals('1200.00', $recurring['amount']);
        $this->assertEquals(1, $recurring['day_of_month']);
    }

    public function test_get_active_returns_only_active(): void
    {
        $user = $this->createTestUser();
        $category = $this->createTestCategory();

        $activeId = $this->model->create([
            'user_id' => $user['id'],
            'category_id' => $category['id'],
            'description' => 'Active subscription',
            'amount' => 9.99,
            'day_of_month' => 15,
            'is_active' => 1,
        ]);
        $this->model->create([
            'user_id' => $user['id'],
            'category_id' => $category['id'],
            'description' => 'Cancelled subscription',
            'amount' => 19.99,
            'day_of_month' => 1,
            'is_active' => 0,
        ]);

        $active = $this->model->getActive();

        // All returned items should be active
        foreach ($active as $item) {
            $this->assertEquals(1, $item['is_active']);
        }

        // Our specific active item should be present
        $ids = array_column($active, 'id');
        $this->assertContains($activeId, $ids);
    }

    public function test_get_due_for_processing(): void
    {
        $user = $this->createTestUser();
        $category = $this->createTestCategory();

        // Never processed - should be due
        $neverProcessedId = $this->model->create([
            'user_id' => $user['id'],
            'category_id' => $category['id'],
            'description' => 'Never processed',
            'amount' => 50.00,
            'day_of_month' => 1,
            'is_active' => 1,
            'last_processed' => null,
        ]);

        // Processed last month - should be due
        $lastMonthId = $this->model->create([
            'user_id' => $user['id'],
            'category_id' => $category['id'],
            'description' => 'Last month processed',
            'amount' => 30.00,
            'day_of_month' => 1,
            'is_active' => 1,
            'last_processed' => '2026-02-15',
        ]);

        // Processed this month - should NOT be due
        $this->model->create([
            'user_id' => $user['id'],
            'category_id' => $category['id'],
            'description' => 'Already processed',
            'amount' => 20.00,
            'day_of_month' => 1,
            'is_active' => 1,
            'last_processed' => '2026-03-01',
        ]);

        // Inactive - should NOT be due
        $this->model->create([
            'user_id' => $user['id'],
            'category_id' => $category['id'],
            'description' => 'Inactive',
            'amount' => 10.00,
            'day_of_month' => 1,
            'is_active' => 0,
            'last_processed' => null,
        ]);

        $due = $this->model->getDueForProcessing('2026-03-22');

        $dueIds = array_column($due, 'id');
        $this->assertContains($neverProcessedId, $dueIds);
        $this->assertContains($lastMonthId, $dueIds);
    }

    public function test_mark_processed(): void
    {
        $user = $this->createTestUser();
        $category = $this->createTestCategory();

        $id = $this->model->create([
            'user_id' => $user['id'],
            'category_id' => $category['id'],
            'description' => 'To be processed',
            'amount' => 25.00,
            'day_of_month' => 1,
            'is_active' => 1,
        ]);

        $this->model->markProcessed($id, '2026-03-22');

        $updated = $this->model->find($id);
        $this->assertEquals('2026-03-22', $updated['last_processed']);
    }
}
