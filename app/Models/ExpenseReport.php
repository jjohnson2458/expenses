<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ExpenseReport extends Model
{
    protected $table = 'expense_reports';

    protected $fillable = [
        'user_id',
        'title',
        'description',
        'status',
        'date_from',
        'date_to',
        'total_amount',
    ];

    protected function casts(): array
    {
        return [
            'total_amount' => 'decimal:2',
            'date_from' => 'date',
            'date_to' => 'date',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function expenses(): HasMany
    {
        return $this->hasMany(Expense::class, 'report_id');
    }

    public static function getWithTotals(): array
    {
        return static::select('expense_reports.*')
            ->selectSub(function ($q) {
                $q->selectRaw('COALESCE(SUM(amount), 0)')
                  ->from('expenses')
                  ->whereColumn('expenses.report_id', 'expense_reports.id');
            }, 'calculated_total')
            ->selectSub(function ($q) {
                $q->selectRaw('COUNT(*)')
                  ->from('expenses')
                  ->whereColumn('expenses.report_id', 'expense_reports.id');
            }, 'expense_count')
            ->orderByDesc('expense_reports.created_at')
            ->get()
            ->toArray();
    }

    public function updateTotal(): void
    {
        $this->update([
            'total_amount' => $this->expenses()->sum('amount'),
        ]);
    }
}
