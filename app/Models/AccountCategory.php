<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AccountCategory extends Model
{
    
protected $fillable = ['key','label','is_system'];

protected $casts = ['is_system' => 'boolean'];


}
