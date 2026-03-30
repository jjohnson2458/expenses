<?php

namespace Tests\Unit\Models;

use Tests\TestCase;
use App\Models\RecurringExpense;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

class RecurringExpenseTest extends TestCase
{
    use RefreshDatabase;

    private function makeRecurring(array $attrs = []): RecurringExpense
    {
        if (!isset($attrs['user_id'])) {
            $attrs['user_id'] = User::factory()->create()->id;
        }
        return RecurringExpense::create(array_merge([
            'description' => 'Monthly Subscription',
            'amount' => 9.99,
            'type' => 'debit',
            'day_of_month' => 15,
            'is_active' => 1,
        ], $attrs));
    }

    public function test_can_create_recurring_expense(): void
    {
        $rec = $this->makeRecurring(['description' => 'Netflix']);
        $this->assertDatabaseHas('recurring_expenses', ['description' => 'Netflix', 'day_of_month' => 15]);
    }

    public function test_active_scope_returns_only_active(): void
    {
        $user = User::factory()->create();
        $this->makeRecurring(['user_id' => $user->id, 'is_active' => 1, 'description' => 'Active']);
        $this->makeRecurring(['user_id' => $user->id, 'is_active' => 0, 'description' => 'Inactive']);

        $active = RecurringExpense::active()->get();
        $this->assertEquals(1, $active->count());
        $this->assertEquals('Active', $active->first()->description);
    }

    /**
     * @group mysql
     * Skipped on SQLite — getDueForProcessing uses MySQL DATE_FORMAT.
     */
    public function test_get_due_for_processing(): void
    {
        if (config('database.default') === 'sqlite') {
            $this->markTestSkipped('getDueForProcessing uses MySQL DATE_FORMAT, incompatible with SQLite.');
        }

        $user = User::factory()->create();
        $this->makeRecurring([
            'user_id' => $user->id,
            'is_active' => 1,
            'last_processed' => null,
        ]);
        $this->makeRecurring([
            'user_id' => $user->id,
            'is_active' => 1,
            'last_processed' => now()->subMonths(2)->format('Y-m-d'),
        ]);
        $this->makeRecurring([
            'user_id' => $user->id,
            'is_active' => 1,
            'last_processed' => now()->format('Y-m-d'),
        ]);

        $due = RecurringExpense::getDueForProcessing(now()->format('Y-m-d'));
        $this->assertEquals(2, count($due));
    }

    public function test_mark_processed(): void
    {
        $rec = $this->makeRecurring(['last_processed' => null]);
        $this->assertNull($rec->last_processed);

        $rec->update(['last_processed' => now()->format('Y-m-d')]);
        $rec->refresh();

        $this->assertNotNull($rec->last_processed);
    }
}
