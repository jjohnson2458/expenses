<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Cashier\Billable;

class User extends Authenticatable
{
    use HasFactory, Notifiable, Billable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'lang',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'password' => 'hashed',
        ];
    }

    public function expenses()
    {
        return $this->hasMany(Expense::class);
    }

    public function reports()
    {
        return $this->hasMany(ExpenseReport::class);
    }

    public function recurringExpenses()
    {
        return $this->hasMany(RecurringExpense::class);
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }
}
