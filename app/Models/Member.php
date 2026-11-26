<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class Member extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id', 'membership_id', 'first_name', 'last_name', 'middle_name',
        'id_number', 'id_type', 'date_of_birth', 'gender', 'marital_status',
        'occupation', 'employer', 'monthly_income', 'physical_address',
        'postal_address', 'city', 'county', 'country', 'emergency_contact_name',
        'emergency_contact_phone', 'emergency_contact_relationship',
        'membership_status', 'membership_date', 'profile_photo', 'documents',
    ];

    protected $casts = [
        'date_of_birth'   => 'date',
        'membership_date' => 'date',
        'monthly_income'  => 'decimal:2',
        'documents'       => 'array',
    ];


    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function accounts()
    {
        return $this->hasMany(Account::class);
    }

    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }

    public function financeConfig()
    {
        return $this->hasOne(MemberFinanceConfig::class);
    }
    
    public function loans()
    {
        return $this->hasMany(Loan::class);
    }

    public function guaranteedLoans()
    {
        return $this->hasMany(LoanGuarantor::class, 'guarantor_member_id');
    }

    public function dividends()
    {
        return $this->hasMany(MemberDividend::class);
    }

    public function nextOfKin()
    {
        return $this->hasMany(MemberNextOfKin::class);
    }

    
    public function depositCommitments()
    {
        return $this->hasMany(MemberDepositCommitment::class);
    }

   
    public function activeDepositCommitments()
    {
        $today = now()->toDateString();

        return $this->hasMany(MemberDepositCommitment::class)
                    ->where('is_active', true)
                    ->where('effective_from', '<=', $today)
                    ->where(function ($q) use ($today) {
                        $q->whereNull('effective_to')
                          ->orWhere('effective_to', '>=', $today);
                    });
    }


    public function getFullNameAttribute(): string
    {
        return trim($this->first_name . ' ' . $this->middle_name . ' ' . $this->last_name);
    }


    public function scopeActive($query)
    {
        return $query->where('membership_status', 'active');
    }
}