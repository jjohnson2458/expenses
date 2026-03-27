<?php

namespace App\Http\Controllers;

use App\Models\Expense;
use App\Models\ErrorLog;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $userId = Auth::id();

        // Stats
        $thisMonth = Expense::where('user_id', $userId)
            ->where('type', 'debit')
            ->whereYear('expense_date', date('Y'))
            ->whereMonth('expense_date', date('m'))
            ->sum('amount');

        $lastMonth = Expense::where('user_id', $userId)
            ->where('type', 'debit')
            ->whereYear('expense_date', date('Y', strtotime('-1 month')))
            ->whereMonth('expense_date', date('m', strtotime('-1 month')))
            ->sum('amount');

        $totalCredits = Expense::where('user_id', $userId)->where('type', 'credit')->sum('amount');
        $totalDebits = Expense::where('user_id', $userId)->where('type', 'debit')->sum('amount');
        $transactionCount = Expense::where('user_id', $userId)->count();

        // Monthly totals with debit/credit split
        $monthlyTotals = DB::table('expenses')
            ->where('user_id', $userId)
            ->selectRaw("DATE_FORMAT(expense_date, '%b %Y') as month,
                COALESCE(SUM(CASE WHEN type='debit' THEN amount ELSE 0 END),0) as debits,
                COALESCE(SUM(CASE WHEN type='credit' THEN amount ELSE 0 END),0) as credits")
            ->whereRaw('expense_date >= DATE_SUB(CURDATE(), INTERVAL 12 MONTH)')
            ->groupByRaw("DATE_FORMAT(expense_date, '%Y-%m'), DATE_FORMAT(expense_date, '%b %Y')")
            ->orderByRaw("DATE_FORMAT(expense_date, '%Y-%m') ASC")
            ->get()
            ->toArray();

        // Recent expenses
        $recentExpenses = DB::table('expenses as e')
            ->leftJoin('expense_categories as c', 'e.category_id', '=', 'c.id')
            ->where('e.user_id', $userId)
            ->select('e.*', 'c.name as category_name', 'c.color as category_color')
            ->orderByDesc('e.expense_date')
            ->orderByDesc('e.created_at')
            ->limit(10)
            ->get();

        // Category breakdown
        $categoryData = DB::table('expenses as e')
            ->leftJoin('expense_categories as c', 'e.category_id', '=', 'c.id')
            ->where('e.user_id', $userId)
            ->where('e.type', 'debit')
            ->select('c.name as name', 'c.color as color', DB::raw('SUM(e.amount) as total'))
            ->groupBy('c.name', 'c.color')
            ->orderByDesc('total')
            ->get();

        $categoryMax = $categoryData->max('total') ?: 1;
        $categoryBreakdown = $categoryData->map(function ($cat) use ($categoryMax) {
            $cat->percentage = round(($cat->total / $categoryMax) * 100, 1);
            $cat->name = $cat->name ?: 'Uncategorized';
            $cat->color = $cat->color ?: '#6c757d';
            return $cat;
        });

        // Upcoming tax deadlines
        $nextDeadline = DB::table('quarterly_estimates')
            ->where('user_id', $userId)
            ->where('due_date', '>=', now())
            ->where('paid_amount', 0)
            ->orderBy('due_date')
            ->first();

        return view('dashboard.index', compact(
            'thisMonth', 'lastMonth', 'totalCredits', 'totalDebits', 'transactionCount',
            'monthlyTotals', 'recentExpenses', 'categoryBreakdown', 'nextDeadline'
        ));
    }

    public function errors()
    {
        $errors = ErrorLog::getRecent(50);

        return view('admin.errors', compact('errors'));
    }

    public function clearErrors()
    {
        ErrorLog::clearAll();

        return back()->with('flash', ['type' => 'success', 'message' => 'All error logs have been cleared.']);
    }
}
