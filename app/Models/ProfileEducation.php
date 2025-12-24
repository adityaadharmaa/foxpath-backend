<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProfileEducation extends Model
{
    protected $fillable = [
        'profiles_id',
        'level',
        'institution_name',
        'nisn',
        'nim',
        'major',
        'gpa',
        'average_score',
        'is_active'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'gpa' => 'decimal:2',
        'average_score' => 'decimal:2'
    ];

    public function profile()
    {
        return $this->belongsTo(Profile::class, 'profiles_id');
    }
}
