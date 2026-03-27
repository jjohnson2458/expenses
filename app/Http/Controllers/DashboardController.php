<?php

namespace App\Http\Controllers;

use App\Models\Expense;
use App\Models\ErrorLog;

class DashboardController extends Controller
{
    public function index()
    {
        $stats = Expense::getDashboardStats();
        $recentExpenses = Expense::getRecentExpenses(10);
        $monthlyTotals = Expense::getMonthlyTotals(12);
        $byCategory = Expense::getTotalByCategory();

        return view('dashboard.index', compact('stats', 'recentExpenses', 'monthlyTotals', 'byCategory'));
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
