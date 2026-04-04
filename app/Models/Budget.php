<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class Budget extends Model
{
    protected $fillable = [
        'user_id',
        'category_id',
        'amount',
        'budget_month',
        'alert_75_sent',
        'alert_90_sent',
        'alert_100_sent',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function scopeForUser($query, ?int $userId = null)
    {
        return $query->where('user_id', $userId ?? Auth::id());
    }

    public function scopeForMonth($query, string $month)
    {
        return $query->where('budget_month', $month);
    }

    /**
     * Get spending for this budget's category and month.
     */
    public function getSpentAttribute(): float
    {
        $parts = explode('-', $this->budget_month);
        $year = $parts[0];
        $month = $parts[1];

        return (float) DB::table('expenses')
            ->where('user_id', $this->user_id)
            ->where('category_id', $this->category_id)
            ->where('type', 'debit')
            ->whereYear('expense_date', $year)
            ->whereMonth('expense_date', $month)
            ->sum('amount');
    }

    public function getPercentUsedAttribute(): float
    {
        if ($this->amount <= 0) {
            return 0;
        }

        return round(($this->spent / $this->amount) * 100, 1);
    }

    public function getRemainingAttribute(): float
    {
        return max(0, $this->amount - $this->spent);
    }

    /**
     * Get budget status with summary for a given user/month.
     */
    public static function summaryForMonth(int $userId, string $month): array
    {
        $budgets = static::where('user_id', $userId)
            ->where('budget_month', $month)
            ->with('category')
            ->get();

        $totalBudgeted = $budgets->sum('amount');
        $totalSpent = $budgets->sum('spent');
        $overBudgetCount = $budgets->filter(fn($b) => $b->percent_used > 100)->count();

        return [
            'budgets' => $budgets,
            'total_budgeted' => $totalBudgeted,
            'total_spent' => $totalSpent,
            'over_budget_count' => $overBudgetCount,
        ];
    }
}
