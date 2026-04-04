<?php

namespace App\Http\Controllers;

use App\Models\RecurringExpense;
use App\Models\Category;
use App\Models\Expense;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class RecurringExpenseController extends Controller
{
    public function index()
    {
        $recurring = DB::table('recurring_expenses as r')
            ->leftJoin('expense_categories as c', 'r.category_id', '=', 'c.id')
            ->select('r.*', 'c.name as category_name', 'c.color as category_color')
            ->orderByDesc('r.is_active')
            ->orderByDesc('r.id')
            ->get();

        return view('expenses.recurring.index', compact('recurring'));
    }

    public function create()
    {
        $categories = Category::active()->forUser()->ordered()->get();

        return view('expenses.recurring.form', ['categories' => $categories, 'recurring' => null]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'description' => 'required|string|max:500',
            'amount' => 'required|numeric',
            'day_of_month' => 'required|integer|min:1|max:31',
        ]);

        RecurringExpense::create($this->buildData($request));

        return redirect('/recurring')->with('flash', ['type' => 'success', 'message' => 'Recurring expense created successfully.']);
    }

    public function edit(int $id)
    {
        $recurring = RecurringExpense::findOrFail($id);
        $categories = Category::active()->forUser()->ordered()->get();

        return view('expenses.recurring.form', compact('recurring', 'categories'));
    }

    public function update(Request $request, int $id)
    {
        $request->validate([
            'description' => 'required|string|max:500',
            'amount' => 'required|numeric',
            'day_of_month' => 'required|integer|min:1|max:31',
        ]);

        $recurring = RecurringExpense::findOrFail($id);
        $recurring->update($this->buildData($request));

        return redirect('/recurring')->with('flash', ['type' => 'success', 'message' => 'Recurring expense updated successfully.']);
    }

    public function destroy(int $id)
    {
        RecurringExpense::findOrFail($id)->delete();

        return redirect('/recurring')->with('flash', ['type' => 'success', 'message' => 'Recurring expense deleted successfully.']);
    }

    public function processMonthly()
    {
        $today = date('Y-m-d');
        $due = RecurringExpense::getDueForProcessing($today);
        $count = 0;

        foreach ($due as $item) {
            Expense::create([
                'description' => $item['description'],
                'amount' => $item['amount'],
                'type' => $item['type'] ?? 'debit',
                'expense_date' => $today,
                'category_id' => $item['category_id'],
                'vendor' => $item['vendor'] ?? null,
                'user_id' => Auth::id(),
                'is_recurring' => true,
            ]);

            RecurringExpense::where('id', $item['id'])->update(['last_processed' => $today]);
            $count++;
        }

        return redirect('/recurring')->with('flash', ['type' => 'success', 'message' => "{$count} recurring expense(s) processed successfully."]);
    }

    private function buildData(Request $request): array
    {
        return [
            'description' => trim($request->description),
            'amount' => (float) $request->amount,
            'type' => in_array($request->type, ['debit', 'credit']) ? $request->type : 'debit',
            'day_of_month' => (int) $request->day_of_month,
            'is_active' => $request->has('is_active') ? 1 : 0,
            'user_id' => Auth::id(),
            'category_id' => $request->category_id ?: null,
            'vendor' => $request->vendor ?: null,
        ];
    }
}
