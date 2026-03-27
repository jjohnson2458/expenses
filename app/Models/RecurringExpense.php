<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RecurringExpense extends Model
{
    protected $table = 'recurring_expenses';

    protected $fillable = [
        'user_id',
        'category_id',
        'type',
        'description',
        'amount',
        'vendor',
        'day_of_month',
        'is_active',
        'last_processed',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'is_active' => 'boolean',
            'day_of_month' => 'integer',
            'last_processed' => 'date',
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

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public static function getDueForProcessing(string $currentDate): array
    {
        return static::where('is_active', true)
            ->where(function ($q) use ($currentDate) {
                $q->whereNull('last_processed')
                  ->orWhereRaw("DATE_FORMAT(last_processed, '%Y-%m') < DATE_FORMAT(?, '%Y-%m')", [$currentDate]);
            })
            ->orderBy('id', 'asc')
            ->get()
            ->toArray();
    }
}
