<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class OtpVerification extends Model
{
    use HasFactory;

    protected $fillable = [
        'identifier', 'otp_code', 'type', 'expires_at', 'is_used', 'used_at'
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'used_at' => 'datetime',
        'is_used' => 'boolean',
    ];

    // Scope for valid OTPs
    public function scopeValid($query)
    {
        return $query->where('expires_at', '>', now())
                    ->where('is_used', false);
    }

    // Scope for expired OTPs
    public function scopeExpired($query)
    {
        return $query->where('expires_at', '<=', now());
    }
}
