<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Budget extends Model
{
    use HasFactory;

    protected $fillable = [
        'budget_year', 'title', 'description', 'total_budget', 'status',
        'start_date', 'end_date', 'created_by', 'approved_by', 'approval_date'
    ];

    protected $casts = [
        'total_budget' => 'decimal:2',
        'start_date' => 'date',
        'end_date' => 'date',
        'approval_date' => 'date',
    ];

    public function budgetItems()
    {
        return $this->hasMany(BudgetItem::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function createdBy()
    {
        return $this->creator();
    }

    public function approvedBy()
    {
        return $this->approver();
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }
}