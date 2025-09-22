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

    // Relationships
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

    // Keep the old method names for backward compatibility if needed
    public function createdBy()
    {
        return $this->creator();
    }

    public function approvedBy()
    {
        return $this->approver();
    }

    // Scope for active budgets
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }
}