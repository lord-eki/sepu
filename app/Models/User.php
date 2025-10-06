<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Contracts\Auth\MustVerifyEmail;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable,HasApiTokens;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name','role',
        'email','phone',
        'password','is_active',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
        ];
    }

     // Relationships
    public function member()
    {
        return $this->hasOne(Member::class);
    }

    public function notifications()
    {
        return $this->hasMany(Notification::class);
    }

    public function auditLogs()
    {
        return $this->hasMany(AuditLog::class);
    }

    public function createdBudgets()
    {
        return $this->hasMany(Budget::class, 'created_by');
    }

    public function approvedBudgets()
    {
        return $this->hasMany(Budget::class, 'approved_by');
    }

    public function createdVouchers()
    {
        return $this->hasMany(PaymentVoucher::class, 'created_by');
    }

    public function approvedVouchers()
    {
        return $this->hasMany(PaymentVoucher::class, 'approved_by');
    }

    public function approvedLoans()
    {
        return $this->hasMany(Loan::class, 'approved_by');
    }

    public function disbursedLoans()
    {
        return $this->hasMany(Loan::class, 'disbursed_by');
    }

    public function calculatedDividends()
    {
        return $this->hasMany(Dividend::class, 'calculated_by');
    }

    public function approvedDividends()
    {
        return $this->hasMany(Dividend::class, 'approved_by');
    }
}
