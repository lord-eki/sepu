<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GuaranteeLock extends Model
{



    protected $fillable = [
        'loan_id',
        'loan_guarantor_id',
        'guarantor_member_id',
        'account_id',
        'amount',
        'status',
        'released_at',
        'release_reason'
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'released_at' => 'datetime'
    ];

    public function loan()
    {
        return $this->belongsTo(Loan::class);
    }

    public function guarantor()
    {
        return $this->belongsTo(LoanGuarantor::class, 'loan_guarantor_id');
    }

    public function account()
    {
        return $this->belongsTo(Account::class);
    }
}
