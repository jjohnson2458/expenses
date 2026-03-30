<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Expense;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ExpenseCrudTest extends TestCase
{
    use RefreshDatabase;

    private function authUser(): User
    {
        $user = User::factory()->create();
        $this->actingAs($user);
        return $user;
    }

    public function test_can_list_expenses(): void
    {
        $this->authUser();
        $response = $this->get('/expenses');
        $response->assertStatus(200);
    }

    public function test_can_create_debit_expense(): void
    {
        $user = $this->authUser();

        $response = $this->post('/expenses', [
            'description' => 'Office Supplies',
            'amount' => 49.99,
            'type' => 'debit',
            'expense_date' => '2026-03-15',
            'vendor' => 'Staples',
        ]);

        $this->assertDatabaseHas('expenses', [
            'description' => 'Office Supplies',
            'type' => 'debit',
            'user_id' => $user->id,
        ]);
    }

    public function test_can_create_credit_expense(): void
    {
        $user = $this->authUser();

        $response = $this->post('/expenses', [
            'description' => 'Client Refund',
            'amount' => 100.00,
            'type' => 'credit',
            'expense_date' => '2026-03-10',
        ]);

        $this->assertDatabaseHas('expenses', ['description' => 'Client Refund', 'type' => 'credit']);
    }

    public function test_can_update_expense(): void
    {
        $user = $this->authUser();
        $expense = Expense::create([
            'user_id' => $user->id, 'description' => 'Old', 'amount' => 10,
            'type' => 'debit', 'expense_date' => '2026-03-01',
        ]);

        $response = $this->post("/expenses/{$expense->id}", [
            'description' => 'Updated Expense',
            'amount' => 75.00,
            'type' => 'debit',
            'expense_date' => '2026-03-01',
        ]);

        $this->assertDatabaseHas('expenses', ['id' => $expense->id, 'description' => 'Updated Expense']);
    }

    public function test_can_delete_expense(): void
    {
        $user = $this->authUser();
        $expense = Expense::create([
            'user_id' => $user->id, 'description' => 'Delete Me', 'amount' => 10,
            'type' => 'debit', 'expense_date' => '2026-03-01',
        ]);

        $response = $this->post("/expenses/{$expense->id}/delete");

        $this->assertDatabaseMissing('expenses', ['id' => $expense->id]);
    }
}
