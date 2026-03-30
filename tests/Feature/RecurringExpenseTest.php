<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\RecurringExpense;
use App\Models\Expense;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

class RecurringExpenseTest extends TestCase
{
    use RefreshDatabase;

    private function authUser(): User
    {
        $user = User::factory()->create();
        $this->actingAs($user);
        return $user;
    }

    public function test_can_list_recurring_expenses(): void
    {
        $this->authUser();
        $response = $this->get('/recurring');
        $response->assertStatus(200);
    }

    public function test_can_create_recurring_expense(): void
    {
        $user = $this->authUser();

        $response = $this->post('/recurring', [
            'description' => 'Netflix',
            'amount' => 15.99,
            'type' => 'debit',
            'day_of_month' => 1,
        ]);

        $this->assertDatabaseHas('recurring_expenses', [
            'description' => 'Netflix',
            'user_id' => $user->id,
        ]);
    }

    /**
     * @group mysql
     * Skipped on SQLite — processMonthly uses MySQL DATE_FORMAT internally.
     */
    public function test_process_monthly_creates_expenses(): void
    {
        if (config('database.default') === 'sqlite') {
            $this->markTestSkipped('processMonthly uses MySQL DATE_FORMAT, incompatible with SQLite.');
        }

        $user = $this->authUser();

        RecurringExpense::create([
            'user_id' => $user->id,
            'description' => 'Spotify',
            'amount' => 9.99,
            'type' => 'debit',
            'day_of_month' => 15,
            'is_active' => 1,
            'last_processed' => null,
        ]);

        $response = $this->post('/recurring/process');

        $this->assertDatabaseHas('expenses', [
            'description' => 'Spotify',
            'user_id' => $user->id,
        ]);
    }
}
