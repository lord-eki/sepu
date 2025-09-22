<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Dividend extends Model
{
    use HasFactory;

    protected $fillable = [
        'dividend_year', 'total_profit', 'dividend_rate', 'total_dividends',
        'status', 'calculation_date', 'approval_date', 'distribution_date',
        'calculated_by', 'approved_by', 'notes'
    ];

    protected $casts = [
        'total_profit' => 'decimal:2',
        'dividend_rate' => 'decimal:4',
        'total_dividends' => 'decimal:2',
        'calculation_date' => 'date',
        'approval_date' => 'date',
        'distribution_date' => 'date',
    ];

    // Relationships
    public function memberDividends()
    {
        return $this->hasMany(MemberDividend::class);
    }

    public function calculatedBy()
    {
        return $this->belongsTo(User::class, 'calculated_by');
    }

    public function approvedBy()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}
