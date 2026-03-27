<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ErrorLog extends Model
{
    protected $table = 'error_logs';

    public $timestamps = false;

    protected $fillable = [
        'message',
        'context',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'created_at' => 'datetime',
        ];
    }

    public static function getRecent(int $limit = 50): array
    {
        return static::orderByDesc('id')
            ->limit($limit)
            ->get()
            ->toArray();
    }

    public static function clearAll(): void
    {
        static::truncate();
    }
}
