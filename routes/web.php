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
use App\Http\Controllers\BillingController;
use App\Http\Controllers\TaxController;
use App\Http\Controllers\AdminTokenUsageController;
use App\Http\Controllers\BudgetController;
use App\Http\Controllers\TaxPackageController;
use App\Http\Controllers\QuarterlyEstimateController;
use Illuminate\Support\Facades\Auth;

// Welcome / Splash
Route::get('/', function () {
    if (Auth::check()) {
        return redirect('/dashboard');
    }
    return view('welcome');
});

// Auth
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
Route::post('/register', [AuthController::class, 'register']);
Route::get('/forgot-password', [AuthController::class, 'showForgotPassword'])->name('password.request');
Route::post('/forgot-password', [AuthController::class, 'forgotPassword'])->name('password.email');
Route::get('/reset-password', [AuthController::class, 'showResetPassword'])->name('password.reset');
Route::post('/reset-password', [AuthController::class, 'resetPassword'])->name('password.update');
Route::post('/demo', [AuthController::class, 'demoLogin'])->name('demo');
Route::match(['get', 'post'], '/logout', [AuthController::class, 'logout'])->name('logout');

// Legal/Static Pages (no auth required)
Route::get('/terms', [PageController::class, 'terms']);
Route::get('/privacy', [PageController::class, 'privacy']);

// Public demo preview
Route::get('/demo', function () {
    return view('demo');
})->name('demo.preview');

// Stripe Webhook
Route::post('/stripe/webhook', [\Laravel\Cashier\Http\Controllers\WebhookController::class, 'handleWebhook'])
    ->name('cashier.webhook');

// Language
Route::get('/lang/{locale}', [LanguageController::class, 'switch']);

// All authenticated routes
Route::middleware('auth')->group(function () {
    // Dashboard
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
    Route::post('/expenses/scan', [ExpenseController::class, 'scanReceipt']);

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
    Route::get('/export/ofx', [ExportController::class, 'ofx']);
    Route::get('/export/qfx', [ExportController::class, 'qfx']);
    Route::get('/export/qbo', [ExportController::class, 'qbo']);
    Route::get('/export/report/{id}/csv', [ExportController::class, 'reportCsv']);

    // Import
    Route::get('/import', [ImportController::class, 'index']);
    Route::post('/import', [ImportController::class, 'process']);

    // Budgets
    Route::get('/budgets', [BudgetController::class, 'index']);
    Route::post('/budgets', [BudgetController::class, 'store']);
    Route::post('/budgets/copy', [BudgetController::class, 'copy']);
    Route::post('/budgets/{id}/delete', [BudgetController::class, 'destroy']);

    // Tax
    Route::get('/tax/profile', [TaxController::class, 'profile']);
    Route::post('/tax/profile', [TaxController::class, 'updateProfile']);
    Route::get('/tax/mileage', [TaxController::class, 'mileage']);
    Route::post('/tax/mileage', [TaxController::class, 'storeMileage']);
    Route::post('/tax/mileage/{id}/delete', [TaxController::class, 'destroyMileage']);
    Route::get('/tax/summary', [TaxController::class, 'summary']);
    Route::get('/tax/package', [TaxPackageController::class, 'index']);
    Route::get('/tax/package/profit-loss', [TaxPackageController::class, 'downloadProfitLoss']);
    Route::get('/tax/package/schedule-c', [TaxPackageController::class, 'downloadScheduleC']);
    Route::get('/tax/package/category-detail', [TaxPackageController::class, 'downloadCategoryDetail']);
    Route::get('/tax/package/mileage', [TaxPackageController::class, 'downloadMileageLog']);
    Route::get('/tax/package/home-office', [TaxPackageController::class, 'downloadHomeOffice']);
    Route::get('/tax/package/turbotax', [TaxPackageController::class, 'downloadTurbotax']);
    Route::get('/tax/package/quickbooks', [TaxPackageController::class, 'downloadQuickbooks']);

    // Quarterly Estimates
    Route::get('/tax/quarterly', [QuarterlyEstimateController::class, 'index']);
    Route::post('/tax/quarterly/generate', [QuarterlyEstimateController::class, 'generate']);
    Route::post('/tax/quarterly/{id}/pay', [QuarterlyEstimateController::class, 'markPaid']);

    // Billing
    Route::get('/billing', [BillingController::class, 'index']);
    Route::post('/billing/subscribe', [BillingController::class, 'subscribe']);
    Route::post('/billing/change', [BillingController::class, 'changePlan']);
    Route::post('/billing/cancel', [BillingController::class, 'cancel']);
    Route::post('/billing/resume', [BillingController::class, 'resume']);
    Route::post('/billing/portal', [BillingController::class, 'portal']);

    // Settings
    Route::get('/settings', [SettingsController::class, 'index']);
    Route::post('/settings', [SettingsController::class, 'update']);

    // Admin
    Route::get('/admin/errors', [DashboardController::class, 'errors']);
    Route::post('/admin/errors/clear', [DashboardController::class, 'clearErrors']);
    Route::get('/admin/token-usage', [AdminTokenUsageController::class, 'index']);
});
