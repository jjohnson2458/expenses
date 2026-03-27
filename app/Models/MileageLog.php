<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MileageLog extends Model
{
    protected $fillable = [
        'user_id', 'trip_date', 'start_location', 'end_location',
        'business_purpose', 'miles', 'irs_rate', 'round_trip',
    ];

    protected function casts(): array
    {
        return [
            'trip_date' => 'date',
            'miles' => 'decimal:1',
            'irs_rate' => 'decimal:4',
            'round_trip' => 'boolean',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function getDeductionAttribute(): float
    {
        return round((float) $this->miles * (float) $this->irs_rate, 2);
    }

    public static function getYearSummary(int $userId, int $year): array
    {
        $logs = self::where('user_id', $userId)
            ->whereYear('trip_date', $year)
            ->orderBy('trip_date')
            ->get();

        return [
            'total_miles' => $logs->sum('miles'),
            'total_deduction' => $logs->sum(fn($l) => $l->deduction),
            'trip_count' => $logs->count(),
            'logs' => $logs,
        ];
    }
}
