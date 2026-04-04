<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    protected $table = 'expense_categories';

    protected $fillable = [
        'name',
        'name_es',
        'description',
        'color',
        'icon',
        'sort_order',
        'is_active',
        'user_id',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    public function expenses()
    {
        return $this->hasMany(Expense::class, 'category_id');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order', 'asc');
    }

    /**
     * Scope to categories visible to the given user:
     * global (user_id IS NULL) OR owned by the user.
     */
    public function scopeForUser($query, ?int $userId = null)
    {
        $uid = $userId ?? (int) \Illuminate\Support\Facades\Auth::id();

        return $query->where(function ($q) use ($uid) {
            $q->whereNull('user_id')->orWhere('user_id', $uid);
        });
    }

    public static function updateSortOrder(array $orders): void
    {
        foreach ($orders as $id => $sortOrder) {
            static::where('id', $id)->update(['sort_order' => $sortOrder]);
        }
    }
}
