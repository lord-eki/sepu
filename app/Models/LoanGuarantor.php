<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class LoanGuarantor extends Model
{
    use HasFactory;

    protected $fillable = [
        'loan_id', 'guarantor_member_id', 'guaranteed_amount',
        'status', 'response_date', 'comments'
    ];

    protected $casts = [
        'guaranteed_amount' => 'decimal:2',
        'response_date' => 'datetime',
    ];

    // Relationships
    public function loan()
    {
        return $this->belongsTo(Loan::class);
    }

    // Rename relationship to match controller/frontend usage
    public function guarantorMember()
    {
        return $this->belongsTo(Member::class, 'guarantor_member_id');
    }
}
