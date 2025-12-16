<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InternshipApplication extends Model
{
    protected $fillable = [
        'programs_id',
        'profiles_id',
        'status',
        'submitted_at',
        'final_score',
        'rank',
        'admitted_at',
        'placement_start_at',
        'placement_end_at'
    ];

    public function program()
    {
        return $this->belongsTo(Program::class);
    }

    public function profile()
    {
        return $this->belongsTo(Profile::class);
    }
}
