<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ExpenseController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\RecurringExpenseController;
use App\Http\Controllers\ExportController;
use App\Http\Controllers\ImportController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\LanguageController;

// Auth
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::get('/logout', [AuthController::class, 'logout'])->name('logout');

// Legal/Static Pages (no auth required)
Route::get('/terms', [PageController::class, 'terms']);
Route::get('/privacy', [PageController::class, 'privacy']);

// Language
Route::get('/lang/{locale}', [LanguageController::class, 'switch']);

// All authenticated routes
Route::middleware('auth')->group(function () {
    // Dashboard
    Route::get('/', [DashboardController::class, 'index']);
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Categories
    Route::get('/categories', [CategoryController::class, 'index']);
    Route::get('/categories/create', [CategoryController::class, 'create']);
    Route::post('/categories', [CategoryController::class, 'store']);
    Route::get('/categories/{id}/edit', [CategoryController::class, 'edit']);
    Route::post('/categories/{id}', [CategoryController::class, 'update']);
    Route::post('/categories/{id}/delete', [CategoryController::class, 'destroy']);
    Route::post('/categories/reorder', [CategoryController::class, 'reorder']);

    // Expenses
    Route::get('/expenses', [ExpenseController::class, 'index']);
    Route::get('/expenses/create', [ExpenseController::class, 'create']);
    Route::post('/expenses', [ExpenseController::class, 'store']);
    Route::get('/expenses/{id}/edit', [ExpenseController::class, 'edit']);
    Route::post('/expenses/{id}', [ExpenseController::class, 'update']);
    Route::post('/expenses/{id}/delete', [ExpenseController::class, 'destroy']);
    Route::post('/expenses/voice', [ExpenseController::class, 'voiceInput']);

    // Reports
    Route::get('/reports', [ReportController::class, 'index']);
    Route::get('/reports/create', [ReportController::class, 'create']);
    Route::post('/reports', [ReportController::class, 'store']);
    Route::get('/reports/{id}', [ReportController::class, 'show']);
    Route::get('/reports/{id}/edit', [ReportController::class, 'edit']);
    Route::post('/reports/{id}', [ReportController::class, 'update']);
    Route::post('/reports/{id}/delete', [ReportController::class, 'destroy']);
    Route::post('/reports/{id}/add-expense', [ReportController::class, 'addExpense']);
    Route::post('/reports/{id}/remove-expense', [ReportController::class, 'removeExpense']);
    Route::get('/reports/{id}/print', [ReportController::class, 'printReport']);

    // Recurring Expenses
    Route::get('/recurring', [RecurringExpenseController::class, 'index']);
    Route::get('/recurring/create', [RecurringExpenseController::class, 'create']);
    Route::post('/recurring', [RecurringExpenseController::class, 'store']);
    Route::get('/recurring/{id}/edit', [RecurringExpenseController::class, 'edit']);
    Route::post('/recurring/{id}', [RecurringExpenseController::class, 'update']);
    Route::post('/recurring/{id}/delete', [RecurringExpenseController::class, 'destroy']);
    Route::post('/recurring/process', [RecurringExpenseController::class, 'processMonthly']);

    // Export
    Route::get('/export/csv', [ExportController::class, 'csv']);
    Route::get('/export/quickbooks', [ExportController::class, 'quickbooks']);
    Route::get('/export/calendar', [ExportController::class, 'googleCalendar']);
    Route::get('/export/report/{id}/csv', [ExportController::class, 'reportCsv']);

    // Import
    Route::get('/import', [ImportController::class, 'index']);
    Route::post('/import', [ImportController::class, 'process']);

    // Settings
    Route::get('/settings', [SettingsController::class, 'index']);
    Route::post('/settings', [SettingsController::class, 'update']);

    // Admin
    Route::get('/admin/errors', [DashboardController::class, 'errors']);
    Route::post('/admin/errors/clear', [DashboardController::class, 'clearErrors']);
});
