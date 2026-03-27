<?php

namespace App\Http\Controllers;

use App\Models\ExpenseReport;
use App\Models\Expense;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    public function index()
    {
        $reports = ExpenseReport::getWithTotals();

        return view('expenses.reports.index', compact('reports'));
    }

    public function create()
    {
        return view('expenses.reports.form', ['report' => null]);
    }

    public function store(Request $request)
    {
        $request->validate(['title' => 'required|string|max:255']);

        ExpenseReport::create([
            'user_id' => Auth::id(),
            'title' => $request->title,
            'description' => $request->description,
            'status' => $request->status ?? 'draft',
            'date_from' => $request->date_from ?: null,
            'date_to' => $request->date_to ?: null,
            'total_amount' => 0,
        ]);

        return redirect('/reports')->with('flash', ['type' => 'success', 'message' => 'Report created successfully.']);
    }

    public function show(int $id)
    {
        $report = ExpenseReport::findOrFail($id);
        $linkedExpenses = Expense::where('report_id', $id)->orderByDesc('expense_date')->get();

        $availableExpenses = DB::table('expenses')
            ->leftJoin('expense_categories', 'expenses.category_id', '=', 'expense_categories.id')
            ->select('expenses.*', 'expense_categories.name as category_name')
            ->whereNull('expenses.report_id')
            ->orderByDesc('expenses.expense_date')
            ->get();

        $categories = Category::active()->get();
        $categoryMap = $categories->pluck('name', 'id')->toArray();

        return view('expenses.reports.show', compact('report', 'linkedExpenses', 'availableExpenses', 'categoryMap'));
    }

    public function edit(int $id)
    {
        $report = ExpenseReport::findOrFail($id);

        return view('expenses.reports.form', compact('report'));
    }

    public function update(Request $request, int $id)
    {
        $request->validate(['title' => 'required|string|max:255']);

        $report = ExpenseReport::findOrFail($id);
        $report->update([
            'title' => $request->title,
            'description' => $request->description,
            'status' => $request->status ?? 'draft',
            'date_from' => $request->date_from ?: null,
            'date_to' => $request->date_to ?: null,
        ]);

        return redirect("/reports/{$id}")->with('flash', ['type' => 'success', 'message' => 'Report updated successfully.']);
    }

    public function destroy(int $id)
    {
        $report = ExpenseReport::findOrFail($id);

        // Unlink expenses
        Expense::where('report_id', $id)->update(['report_id' => null]);

        $report->delete();

        return redirect('/reports')->with('flash', ['type' => 'success', 'message' => 'Report deleted successfully.']);
    }

    public function addExpense(Request $request, int $id)
    {
        $expenseId = (int) $request->expense_id;
        if ($expenseId <= 0) {
            return redirect("/reports/{$id}")->with('flash', ['type' => 'danger', 'message' => 'No expense selected.']);
        }

        Expense::where('id', $expenseId)->update(['report_id' => $id]);
        ExpenseReport::find($id)?->updateTotal();

        return redirect("/reports/{$id}")->with('flash', ['type' => 'success', 'message' => 'Expense added to report.']);
    }

    public function removeExpense(Request $request, int $id)
    {
        $expenseId = (int) $request->expense_id;
        if ($expenseId <= 0) {
            return redirect("/reports/{$id}")->with('flash', ['type' => 'danger', 'message' => 'No expense specified.']);
        }

        Expense::where('id', $expenseId)->where('report_id', $id)->update(['report_id' => null]);
        ExpenseReport::find($id)?->updateTotal();

        return redirect("/reports/{$id}")->with('flash', ['type' => 'success', 'message' => 'Expense removed from report.']);
    }

    public function printReport(int $id)
    {
        $report = ExpenseReport::findOrFail($id);
        $linkedExpenses = Expense::where('report_id', $id)->orderByDesc('expense_date')->get();

        $categories = Category::active()->get();
        $categoryMap = $categories->pluck('name', 'id')->toArray();

        return view('expenses.reports.print', compact('report', 'linkedExpenses', 'categoryMap'));
    }
}
