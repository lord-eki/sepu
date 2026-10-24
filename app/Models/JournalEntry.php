<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class JournalEntry extends Model
{
     use HasFactory, SoftDeletes;

    protected $fillable = [
        'entry_number', 'idempotency_key','reversal_of_id',
        'entry_date','reference_type','reference_id',
        'description','total_debit','total_credit','status',
        'created_by','posted_by','posted_at',
    ];

    protected $casts = [
        'entry_date' => 'date',
        'total_debit' => 'decimal:2',
        'total_credit' => 'decimal:2',
        'posted_at' => 'datetime',
    ];

    public function lines()
    {
        return $this->hasMany(JournalEntryLine::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function poster()
    {
        return $this->belongsTo(User::class, 'posted_by');
    }

    public function isBalanced()
    {
        return round($this->total_debit, 2) == round($this->total_credit, 2);
    }

}
