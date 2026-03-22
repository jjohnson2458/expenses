<?php
/**
 * Web Routes
 *
 * @author J.J. Johnson <visionquest716@gmail.com>
 */

use App\Helpers\Router;
use App\Controllers\AuthController;
use App\Controllers\DashboardController;
use App\Controllers\CategoryController;
use App\Controllers\ExpenseController;
use App\Controllers\ReportController;
use App\Controllers\RecurringExpenseController;
use App\Controllers\ExportController;
use App\Controllers\ImportController;
use App\Controllers\SettingsController;
use App\Controllers\PageController;
use App\Controllers\LanguageController;

// Auth
Router::get('/login', [AuthController::class, 'showLogin']);
Router::post('/login', [AuthController::class, 'login']);
Router::get('/logout', [AuthController::class, 'logout']);

// Dashboard
Router::get('/', [DashboardController::class, 'index']);
Router::get('/dashboard', [DashboardController::class, 'index']);

// Expense Categories CRUD
Router::get('/categories', [CategoryController::class, 'index']);
Router::get('/categories/create', [CategoryController::class, 'create']);
Router::post('/categories', [CategoryController::class, 'store']);
Router::get('/categories/{id}/edit', [CategoryController::class, 'edit']);
Router::post('/categories/{id}', [CategoryController::class, 'update']);
Router::post('/categories/{id}/delete', [CategoryController::class, 'destroy']);
Router::post('/categories/reorder', [CategoryController::class, 'reorder']);

// Expenses (Ledger)
Router::get('/expenses', [ExpenseController::class, 'index']);
Router::get('/expenses/create', [ExpenseController::class, 'create']);
Router::post('/expenses', [ExpenseController::class, 'store']);
Router::get('/expenses/{id}/edit', [ExpenseController::class, 'edit']);
Router::post('/expenses/{id}', [ExpenseController::class, 'update']);
Router::post('/expenses/{id}/delete', [ExpenseController::class, 'destroy']);
Router::post('/expenses/voice', [ExpenseController::class, 'voiceInput']);

// Expense Reports
Router::get('/reports', [ReportController::class, 'index']);
Router::get('/reports/create', [ReportController::class, 'create']);
Router::post('/reports', [ReportController::class, 'store']);
Router::get('/reports/{id}', [ReportController::class, 'show']);
Router::get('/reports/{id}/edit', [ReportController::class, 'edit']);
Router::post('/reports/{id}', [ReportController::class, 'update']);
Router::post('/reports/{id}/delete', [ReportController::class, 'destroy']);
Router::post('/reports/{id}/add-expense', [ReportController::class, 'addExpense']);
Router::post('/reports/{id}/remove-expense', [ReportController::class, 'removeExpense']);
Router::get('/reports/{id}/print', [ReportController::class, 'printReport']);

// Recurring Expenses
Router::get('/recurring', [RecurringExpenseController::class, 'index']);
Router::get('/recurring/create', [RecurringExpenseController::class, 'create']);
Router::post('/recurring', [RecurringExpenseController::class, 'store']);
Router::get('/recurring/{id}/edit', [RecurringExpenseController::class, 'edit']);
Router::post('/recurring/{id}', [RecurringExpenseController::class, 'update']);
Router::post('/recurring/{id}/delete', [RecurringExpenseController::class, 'destroy']);
Router::post('/recurring/process', [RecurringExpenseController::class, 'processMonthly']);

// Export
Router::get('/export/csv', [ExportController::class, 'csv']);
Router::get('/export/quickbooks', [ExportController::class, 'quickbooks']);
Router::get('/export/calendar', [ExportController::class, 'googleCalendar']);
Router::get('/export/report/{id}/csv', [ExportController::class, 'reportCsv']);

// Import
Router::get('/import', [ImportController::class, 'index']);
Router::post('/import', [ImportController::class, 'process']);

// Settings
Router::get('/settings', [SettingsController::class, 'index']);
Router::post('/settings', [SettingsController::class, 'update']);

// Language
Router::get('/lang/{locale}', [LanguageController::class, 'switch']);

// Legal/Static Pages
Router::get('/terms', [PageController::class, 'terms']);
Router::get('/privacy', [PageController::class, 'privacy']);

// Error logs (admin)
Router::get('/admin/errors', [DashboardController::class, 'errors']);
Router::post('/admin/errors/clear', [DashboardController::class, 'clearErrors']);
