<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Criteria extends Model
{
    protected $fillable = [
        'code',
        'name',
        'weight',
        'type',
        'is_active'
    ];

    protected $casts = [
        'weight' => 'float',
        'is_active' => 'boolean',
    ];

    public function scores()
    {
        return $this->hasMany(ApplicationScore::class);
    }
}
