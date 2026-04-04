<?php

namespace App\Http\Controllers;

use App\Models\Budget;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;

class MonthlySummaryController extends Controller
{
    public function index(Request $request)
    {
        $userId = Auth::id();
        $month = $request->get('month', date('Y-m', strtotime('-1 month')));
        $parts = explode('-', $month);
        $year = $parts[0];
        $mo = $parts[1];
        $displayMonth = date('F Y', strtotime("{$month}-01"));

        $totalSpend = DB::table('expenses')
            ->where('user_id', $userId)
            ->where('type', 'debit')
            ->whereYear('expense_date', $year)
            ->whereMonth('expense_date', $mo)
            ->sum('amount');

        $totalCredits = DB::table('expenses')
            ->where('user_id', $userId)
            ->where('type', 'credit')
            ->whereYear('expense_date', $year)
            ->whereMonth('expense_date', $mo)
            ->sum('amount');

        $transactionCount = DB::table('expenses')
            ->where('user_id', $userId)
            ->whereYear('expense_date', $year)
            ->whereMonth('expense_date', $mo)
            ->count();

        $categories = DB::table('expenses as e')
            ->leftJoin('expense_categories as c', 'e.category_id', '=', 'c.id')
            ->where('e.user_id', $userId)
            ->where('e.type', 'debit')
            ->whereYear('e.expense_date', $year)
            ->whereMonth('e.expense_date', $mo)
            ->select('c.name as category_name', 'c.color', DB::raw('SUM(e.amount) as total'))
            ->groupBy('c.name', 'c.color')
            ->orderByDesc('total')
            ->get();

        $budgetSummary = Budget::summaryForMonth($userId, $month);

        // Monthly trend (last 6 months)
        $trend = DB::table('expenses')
            ->where('user_id', $userId)
            ->where('type', 'debit')
            ->whereRaw('expense_date >= DATE_SUB(?, INTERVAL 6 MONTH)', ["{$month}-01"])
            ->whereRaw('expense_date < DATE_ADD(?, INTERVAL 1 MONTH)', ["{$month}-01"])
            ->selectRaw("DATE_FORMAT(expense_date, '%Y-%m') as month, SUM(amount) as total")
            ->groupByRaw("DATE_FORMAT(expense_date, '%Y-%m')")
            ->orderBy('month')
            ->get();

        return view('reports.monthly-summary', compact(
            'month', 'displayMonth', 'totalSpend', 'totalCredits',
            'transactionCount', 'categories', 'budgetSummary', 'trend'
        ));
    }

    public function send(Request $request)
    {
        $userId = Auth::id();
        Artisan::call('expenses:monthly-summary', ['--user' => $userId]);

        return back()->with('flash', [
            'type' => 'success',
            'message' => 'Monthly summary email sent.',
        ]);
    }
}
