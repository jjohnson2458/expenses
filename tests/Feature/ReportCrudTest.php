<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Expense;
use App\Models\ExpenseReport;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ReportCrudTest extends TestCase
{
    use RefreshDatabase;

    private function authUser(): User
    {
        $user = User::factory()->create();
        $this->actingAs($user);
        return $user;
    }

    public function test_can_list_reports(): void
    {
        $this->authUser();
        $response = $this->get('/reports');
        $response->assertStatus(200);
    }

    public function test_can_create_report(): void
    {
        $user = $this->authUser();

        $response = $this->post('/reports', [
            'title' => 'March Expenses',
            'date_from' => '2026-03-01',
            'date_to' => '2026-03-31',
        ]);

        $this->assertDatabaseHas('expense_reports', ['title' => 'March Expenses', 'user_id' => $user->id]);
    }

    public function test_can_add_expense_to_report(): void
    {
        $user = $this->authUser();
        $report = ExpenseReport::create([
            'user_id' => $user->id, 'title' => 'Test Report', 'status' => 'draft',
            'date_from' => '2026-03-01', 'date_to' => '2026-03-31', 'total_amount' => 0,
        ]);
        $expense = Expense::create([
            'user_id' => $user->id, 'description' => 'Test', 'amount' => 50,
            'type' => 'debit', 'expense_date' => '2026-03-10',
        ]);

        $response = $this->post("/reports/{$report->id}/add-expense", [
            'expense_id' => $expense->id,
        ]);

        $this->assertDatabaseHas('expenses', ['id' => $expense->id, 'report_id' => $report->id]);
    }

    public function test_can_remove_expense_from_report(): void
    {
        $user = $this->authUser();
        $report = ExpenseReport::create([
            'user_id' => $user->id, 'title' => 'Test Report', 'status' => 'draft',
            'date_from' => '2026-03-01', 'date_to' => '2026-03-31', 'total_amount' => 0,
        ]);
        $expense = Expense::create([
            'user_id' => $user->id, 'report_id' => $report->id,
            'description' => 'Test', 'amount' => 50,
            'type' => 'debit', 'expense_date' => '2026-03-10',
        ]);

        $response = $this->post("/reports/{$report->id}/remove-expense", [
            'expense_id' => $expense->id,
        ]);

        $this->assertDatabaseHas('expenses', ['id' => $expense->id, 'report_id' => null]);
    }
}
