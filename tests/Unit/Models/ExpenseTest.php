<?php

namespace Tests\Unit\Models;

use Tests\TestCase;
use App\Models\Expense;
use App\Models\Category;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ExpenseTest extends TestCase
{
    use RefreshDatabase;

    private function makeExpense(array $attrs = []): Expense
    {
        if (!isset($attrs['user_id'])) {
            $attrs['user_id'] = User::factory()->create()->id;
        }
        return Expense::create(array_merge([
            'description' => 'Test Expense',
            'amount' => 25.50,
            'type' => 'debit',
            'expense_date' => now()->format('Y-m-d'),
            'vendor' => 'Test Vendor',
        ], $attrs));
    }

    public function test_can_create_expense(): void
    {
        $expense = $this->makeExpense(['description' => 'Office Supplies']);
        $this->assertDatabaseHas('expenses', ['description' => 'Office Supplies', 'type' => 'debit']);
    }

    public function test_can_create_credit_expense(): void
    {
        $expense = $this->makeExpense(['type' => 'credit', 'description' => 'Refund']);
        $this->assertDatabaseHas('expenses', ['description' => 'Refund', 'type' => 'credit']);
    }

    public function test_can_find_expense_by_id(): void
    {
        $expense = $this->makeExpense();
        $found = Expense::find($expense->id);
        $this->assertNotNull($found);
        $this->assertEquals($expense->description, $found->description);
    }

    public function test_can_update_expense(): void
    {
        $expense = $this->makeExpense();
        $expense->update(['description' => 'Updated', 'amount' => 99.99]);
        $this->assertDatabaseHas('expenses', ['id' => $expense->id, 'description' => 'Updated']);
    }

    public function test_can_delete_expense(): void
    {
        $expense = $this->makeExpense();
        $id = $expense->id;
        $expense->delete();
        $this->assertDatabaseMissing('expenses', ['id' => $id]);
    }

    public function test_get_by_date_range(): void
    {
        $user = User::factory()->create();
        $this->makeExpense(['user_id' => $user->id, 'expense_date' => '2026-01-15']);
        $this->makeExpense(['user_id' => $user->id, 'expense_date' => '2026-02-15']);
        $this->makeExpense(['user_id' => $user->id, 'expense_date' => '2026-03-15']);

        $results = Expense::where('user_id', $user->id)
            ->whereBetween('expense_date', ['2026-01-01', '2026-02-28'])
            ->get();

        $this->assertEquals(2, $results->count());
    }

    public function test_debits_scope(): void
    {
        $user = User::factory()->create();
        $this->makeExpense(['user_id' => $user->id, 'type' => 'debit']);
        $this->makeExpense(['user_id' => $user->id, 'type' => 'credit']);

        $this->assertEquals(1, Expense::where('user_id', $user->id)->debits()->count());
    }

    public function test_credits_scope(): void
    {
        $user = User::factory()->create();
        $this->makeExpense(['user_id' => $user->id, 'type' => 'debit']);
        $this->makeExpense(['user_id' => $user->id, 'type' => 'credit']);

        $this->assertEquals(1, Expense::where('user_id', $user->id)->credits()->count());
    }

    public function test_belongs_to_user(): void
    {
        $user = User::factory()->create();
        $expense = $this->makeExpense(['user_id' => $user->id]);

        $this->assertInstanceOf(User::class, $expense->user);
        $this->assertEquals($user->id, $expense->user->id);
    }

    public function test_belongs_to_category(): void
    {
        $user = User::factory()->create();
        $cat = Category::create(['name' => 'Food', 'user_id' => $user->id, 'is_active' => 1, 'sort_order' => 0]);
        $expense = $this->makeExpense(['user_id' => $user->id, 'category_id' => $cat->id]);

        $this->assertInstanceOf(Category::class, $expense->category);
        $this->assertEquals('Food', $expense->category->name);
    }

    public function test_fitid_stored_for_ofx_imports(): void
    {
        $expense = $this->makeExpense(['fitid' => 'ABC123456']);
        $this->assertDatabaseHas('expenses', ['fitid' => 'ABC123456']);
    }
}
