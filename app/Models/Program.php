<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Program extends Model
{
    public $timestamps = true;
    protected $fillable = [
        'name',
        'description',
        'capacity',
        'registration_starts_at',
        'registration_ends_at',
        'cohot_starts_at',
        'placement_duration_months',
        'is_active',
    ];

    protected $casts = [
        'registration_starts_at' => 'datetime',
        'registration_ends_at' => 'datetime',
        'cohort_starts_at' => 'datetime',
        'is_active' => 'boolean'
    ];
}
