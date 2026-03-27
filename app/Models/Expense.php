<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Expense extends Model
{
    protected $fillable = [
        'user_id',
        'category_id',
        'report_id',
        'type',
        'description',
        'amount',
        'expense_date',
        'vendor',
        'receipt_path',
        'notes',
        'is_recurring',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'expense_date' => 'date',
            'is_recurring' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'category_id');
    }

    public function report(): BelongsTo
    {
        return $this->belongsTo(ExpenseReport::class, 'report_id');
    }

    public function scopeDebits($query)
    {
        return $query->where('type', 'debit');
    }

    public function scopeCredits($query)
    {
        return $query->where('type', 'credit');
    }

    public static function getDashboardStats(): array
    {
        $result = static::selectRaw("
            COALESCE(SUM(CASE WHEN DATE_FORMAT(expense_date, '%Y-%m') = DATE_FORMAT(CURDATE(), '%Y-%m') THEN amount ELSE 0 END), 0) AS total_this_month,
            COALESCE(SUM(CASE WHEN DATE_FORMAT(expense_date, '%Y-%m') = DATE_FORMAT(DATE_SUB(CURDATE(), INTERVAL 1 MONTH), '%Y-%m') THEN amount ELSE 0 END), 0) AS total_last_month,
            COALESCE(SUM(CASE WHEN amount > 0 THEN amount ELSE 0 END), 0) AS total_credits,
            COALESCE(SUM(CASE WHEN amount < 0 THEN ABS(amount) ELSE 0 END), 0) AS total_debits,
            COUNT(*) AS count
        ")->first();

        return $result ? $result->toArray() : [];
    }

    public static function getMonthlyTotals(int $months = 12): array
    {
        return static::selectRaw("
            DATE_FORMAT(expense_date, '%Y-%m') AS month,
            SUM(amount) AS total_amount,
            COUNT(*) AS expense_count
        ")
            ->whereRaw('expense_date >= DATE_SUB(CURDATE(), INTERVAL ? MONTH)', [$months])
            ->groupByRaw("DATE_FORMAT(expense_date, '%Y-%m')")
            ->orderBy('month', 'asc')
            ->get()
            ->toArray();
    }

    public static function getTotalByCategory(?string $from = null, ?string $to = null): array
    {
        $query = static::selectRaw('
            expenses.category_id,
            expense_categories.name AS category_name,
            SUM(expenses.amount) AS total_amount,
            COUNT(*) AS expense_count
        ')
            ->leftJoin('expense_categories', 'expenses.category_id', '=', 'expense_categories.id');

        if ($from) {
            $query->where('expenses.expense_date', '>=', $from);
        }
        if ($to) {
            $query->where('expenses.expense_date', '<=', $to);
        }

        return $query->groupBy('expenses.category_id', 'expense_categories.name')
            ->orderByDesc('total_amount')
            ->get()
            ->toArray();
    }

    public static function getRecentExpenses(int $limit = 10): array
    {
        return static::select('expenses.*', 'expense_categories.name as category_name')
            ->leftJoin('expense_categories', 'expenses.category_id', '=', 'expense_categories.id')
            ->orderByDesc('expenses.expense_date')
            ->orderByDesc('expenses.created_at')
            ->limit($limit)
            ->get()
            ->toArray();
    }
}
