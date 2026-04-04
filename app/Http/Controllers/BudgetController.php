<?php

namespace App\Http\Controllers;

use App\Models\Budget;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BudgetController extends Controller
{
    public function index(Request $request)
    {
        $month = $request->get('month', date('Y-m'));
        $userId = Auth::id();

        $summary = Budget::summaryForMonth($userId, $month);
        $categories = Category::active()->forUser()->ordered()->get();

        // Build a map of category_id => budget for the form
        $budgetMap = $summary['budgets']->keyBy('category_id');

        return view('budgets.index', compact('summary', 'categories', 'budgetMap', 'month'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'month' => 'required|regex:/^\d{4}-\d{2}$/',
            'budgets' => 'required|array',
            'budgets.*.category_id' => 'required|integer|exists:expense_categories,id',
            'budgets.*.amount' => 'required|numeric|min:0',
        ]);

        $userId = Auth::id();
        $month = $request->input('month');
        $saved = 0;

        foreach ($request->input('budgets') as $entry) {
            $amount = (float) $entry['amount'];
            if ($amount <= 0) {
                // Delete budget if amount set to 0
                Budget::where('user_id', $userId)
                    ->where('category_id', $entry['category_id'])
                    ->where('budget_month', $month)
                    ->delete();
                continue;
            }

            Budget::updateOrCreate(
                [
                    'user_id' => $userId,
                    'category_id' => $entry['category_id'],
                    'budget_month' => $month,
                ],
                [
                    'amount' => $amount,
                ]
            );
            $saved++;
        }

        return redirect('/budgets?month=' . $month)->with('flash', [
            'type' => 'success',
            'message' => "{$saved} budget(s) saved for {$month}.",
        ]);
    }

    public function copy(Request $request)
    {
        $request->validate([
            'from_month' => 'required|regex:/^\d{4}-\d{2}$/',
            'to_month' => 'required|regex:/^\d{4}-\d{2}$/',
        ]);

        $userId = Auth::id();
        $sourceBudgets = Budget::where('user_id', $userId)
            ->where('budget_month', $request->input('from_month'))
            ->get();

        if ($sourceBudgets->isEmpty()) {
            return back()->with('flash', ['type' => 'warning', 'message' => 'No budgets found for the source month.']);
        }

        $copied = 0;
        foreach ($sourceBudgets as $budget) {
            Budget::updateOrCreate(
                [
                    'user_id' => $userId,
                    'category_id' => $budget->category_id,
                    'budget_month' => $request->input('to_month'),
                ],
                [
                    'amount' => $budget->amount,
                ]
            );
            $copied++;
        }

        return redirect('/budgets?month=' . $request->input('to_month'))->with('flash', [
            'type' => 'success',
            'message' => "{$copied} budget(s) copied to {$request->input('to_month')}.",
        ]);
    }

    public function destroy(Request $request, int $id)
    {
        $budget = Budget::where('id', $id)->where('user_id', Auth::id())->firstOrFail();
        $month = $budget->budget_month;
        $budget->delete();

        return redirect('/budgets?month=' . $month)->with('flash', [
            'type' => 'success',
            'message' => 'Budget removed.',
        ]);
    }
}
