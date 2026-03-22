<?php
/**
 * Dashboard Controller
 *
 * @author J.J. Johnson <visionquest716@gmail.com>
 */

namespace App\Controllers;

use App\Models\Expense;
use App\Models\ErrorLog;

class DashboardController extends Controller
{
    /**
     * Show the main dashboard with stats, charts, and recent expenses
     */
    public function index(): void
    {
        $this->requireAuth();

        $expense = new Expense();

        $stats          = $expense->getDashboardStats();
        $recentExpenses = $expense->getRecentExpenses(10);
        $monthlyTotals  = $expense->getMonthlyTotals(12);
        $byCategory     = $expense->getTotalByCategory();

        $this->view('dashboard.index', [
            'stats'          => $stats,
            'recentExpenses' => $recentExpenses,
            'monthlyTotals'  => $monthlyTotals,
            'byCategory'     => $byCategory,
        ]);
    }

    /**
     * Show the error log (admin only)
     */
    public function errors(): void
    {
        $this->requireAuth();

        $errorLog = new ErrorLog();
        $errors   = $errorLog->getRecent(50);

        $this->view('admin.errors', [
            'errors' => $errors,
        ]);
    }

    /**
     * Clear all error log entries
     */
    public function clearErrors(): void
    {
        $this->requireAuth();
        $this->verifyCsrf();

        $errorLog = new ErrorLog();
        $errorLog->clearAll();

        $this->setFlash('success', 'All error logs have been cleared.');
        $this->back();
    }
}
