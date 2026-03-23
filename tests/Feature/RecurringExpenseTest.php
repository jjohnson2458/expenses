<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\RecurringExpense;
use App\Models\Expense;

class RecurringExpenseTest extends TestCase
{
    private RecurringExpense $recurringModel;
    private Expense $expenseModel;

    protected function setUp(): void
    {
        parent::setUp();
        $this->recurringModel = new RecurringExpense();
        $this->expenseModel = new Expense();
        $this->actingAsUser();
    }

    public function test_can_create_recurring_expense(): void
    {
        $user = $this->createTestUser();
        $category = $this->createTestCategory();

        $id = $this->recurringModel->create([
            'user_id' => $user['id'],
            'category_id' => $category['id'],
            'type' => 'debit',
            'description' => 'Netflix subscription',
            'amount' => 15.99,
            'vendor' => 'Netflix',
            'day_of_month' => 5,
            'is_active' => 1,
        ]);

        $recurring = $this->recurringModel->find($id);
        $this->assertNotNull($recurring);
        $this->assertEquals('Netflix subscription', $recurring['description']);
        $this->assertEquals('15.99', $recurring['amount']);
        $this->assertEquals(5, $recurring['day_of_month']);
        $this->assertEquals(1, $recurring['is_active']);
    }

    public function test_process_monthly_creates_expenses(): void
    {
        $user = $this->createTestUser();
        $category = $this->createTestCategory();

        // Set session user for the processing
        $_SESSION['user_id'] = $user['id'];

        // Create two active recurring expenses that are due
        $rec1Id = $this->recurringModel->create([
            'user_id' => $user['id'],
            'category_id' => $category['id'],
            'type' => 'debit',
            'description' => 'Internet bill',
            'amount' => 79.99,
            'vendor' => 'Comcast',
            'day_of_month' => 1,
            'is_active' => 1,
        ]);

        $rec2Id = $this->recurringModel->create([
            'user_id' => $user['id'],
            'category_id' => $category['id'],
            'type' => 'debit',
            'description' => 'Gym membership',
            'amount' => 39.99,
            'vendor' => 'Planet Fitness',
            'day_of_month' => 15,
            'is_active' => 1,
        ]);

        $today = '2026-03-22';

        // Get due items and process them (simulating RecurringExpenseController::processMonthly)
        $due = $this->recurringModel->getDueForProcessing($today);

        // Our two items should be in the due list
        $dueIds = array_column($due, 'id');
        $this->assertContains($rec1Id, $dueIds);
        $this->assertContains($rec2Id, $dueIds);

        $count = 0;
        foreach ($due as $item) {
            // Only process our test items
            if (!in_array($item['id'], [$rec1Id, $rec2Id])) {
                continue;
            }

            $this->expenseModel->create([
                'description' => $item['description'],
                'amount' => $item['amount'],
                'type' => $item['type'] ?? 'debit',
                'expense_date' => $today,
                'category_id' => $item['category_id'],
                'vendor' => $item['vendor'] ?? null,
                'user_id' => $_SESSION['user_id'],
                'is_recurring' => 1,
            ]);

            $this->recurringModel->markProcessed((int) $item['id'], $today);
            $count++;
        }

        $this->assertEquals(2, $count);

        // Verify recurring expenses are marked as processed
        $rec1 = $this->recurringModel->find($rec1Id);
        $rec2 = $this->recurringModel->find($rec2Id);
        $this->assertEquals($today, $rec1['last_processed']);
        $this->assertEquals($today, $rec2['last_processed']);

        // Verify they are no longer due
        $dueAgain = $this->recurringModel->getDueForProcessing($today);
        $dueAgainIds = array_column($dueAgain, 'id');
        $this->assertNotContains($rec1Id, $dueAgainIds);
        $this->assertNotContains($rec2Id, $dueAgainIds);
    }

    public function test_process_monthly_skips_inactive(): void
    {
        $user = $this->createTestUser();
        $category = $this->createTestCategory();

        $_SESSION['user_id'] = $user['id'];

        // Create one active, one inactive
        $activeId = $this->recurringModel->create([
            'user_id' => $user['id'],
            'category_id' => $category['id'],
            'description' => 'Active subscription',
            'amount' => 10.00,
            'day_of_month' => 1,
            'is_active' => 1,
        ]);

        $inactiveId = $this->recurringModel->create([
            'user_id' => $user['id'],
            'category_id' => $category['id'],
            'description' => 'Cancelled subscription',
            'amount' => 20.00,
            'day_of_month' => 1,
            'is_active' => 0,
        ]);

        $today = '2026-03-22';
        $due = $this->recurringModel->getDueForProcessing($today);

        // Active one should be due, inactive should not
        $dueIds = array_column($due, 'id');
        $this->assertContains($activeId, $dueIds);
        $this->assertNotContains($inactiveId, $dueIds);
    }
}
