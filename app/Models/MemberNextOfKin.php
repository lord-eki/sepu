<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class MemberNextOfKin extends Model
{
    use HasFactory;

    protected $table = 'member_next_of_kin';

    protected $fillable = [
        'member_id', 'name', 'relationship', 'phone', 'email',
        'address', 'allocation_percentage', 'is_primary'
    ];

    protected $casts = [
        'allocation_percentage' => 'decimal:2',
        'is_primary' => 'boolean',
    ];

    // Relationships
    public function member()
    {
        return $this->belongsTo(Member::class);
    }

    // Scope for primary next of kin
    public function scopePrimary($query)
    {
        return $query->where('is_primary', true);
    }
}
