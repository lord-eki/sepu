<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class SystemSetting extends Model
{
     use HasFactory;

    protected $fillable = [
        'key', 'value', 'type', 'description', 'group', 'is_public'
    ];

    protected $casts = [
        'is_public' => 'boolean',
    ];

    public function getTypedValueAttribute()
    {
        switch ($this->type) {
            case 'boolean':
                return filter_var($this->value, FILTER_VALIDATE_BOOLEAN);
            case 'number':
                return is_numeric($this->value) ? (float) $this->value : $this->value;
            case 'json':
                return json_decode($this->value, true);
            default:
                return $this->value;
        }
    }

    public function scopePublic($query)
    {
        return $query->where('is_public', true);
    }

    public function scopeByGroup($query, $group)
    {
        return $query->where('group', $group);
    }
}
