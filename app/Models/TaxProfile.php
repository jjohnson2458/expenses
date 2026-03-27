<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TaxProfile extends Model
{
    protected $fillable = [
        'user_id', 'filing_status', 'state', 'business_entity',
        'business_name', 'ein', 'fiscal_year_start',
        'track_mileage', 'home_office', 'home_office_sqft', 'home_total_sqft',
    ];

    protected function casts(): array
    {
        return [
            'track_mileage' => 'boolean',
            'home_office' => 'boolean',
            'home_office_sqft' => 'decimal:2',
            'home_total_sqft' => 'decimal:2',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function homeOfficeDeduction(): float
    {
        if (!$this->home_office || !$this->home_office_sqft) {
            return 0;
        }

        // Simplified method: $5/sqft, max 300 sqft
        $sqft = min((float) $this->home_office_sqft, 300);
        return $sqft * 5;
    }

    public function homeOfficePercentage(): float
    {
        if (!$this->home_office || !$this->home_office_sqft || !$this->home_total_sqft) {
            return 0;
        }

        return round(((float) $this->home_office_sqft / (float) $this->home_total_sqft) * 100, 2);
    }
}
