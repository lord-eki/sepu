<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;


class DividendSetting extends Model
{
    protected $fillable = [
        'key',
        'value',
        'label',
        'description',
    ];

    protected $casts = [
        'value' => 'float',
    ];

    public static function allAsArray(): array
    {
        return static::pluck('value', 'key')->toArray();
    }

    /**
     * Upsert a single setting by key.
     */
    public static function set(string $key, float $value): static
    {
        return static::updateOrCreate(
            ['key' => $key],
            ['value' => $value]
        );
    }
}