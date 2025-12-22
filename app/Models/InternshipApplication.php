<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InternshipApplication extends Model
{
    protected $fillable = [
        'programs_id',
        'users_id',
        'status',
        'submitted_at',
        'verified_at',
        'scored_at',
        'decided_at',
        'final_score',
        'rank',
        'admitted_at',
        'placement_start_at',
        'placement_end_at'
    ];

    public function program()
    {
        return $this->belongsTo(Program::class, 'programs_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'users_id');
    }

    public function documents()
    {
        return $this->hasMany(ApplicationDocument::class, 'internship_applications_id');
    }
}
