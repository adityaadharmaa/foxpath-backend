<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Profile extends Model
{
    protected $fillable = [
        'users_id',
        'applicant_type',
        'student_identifier',
        'full_name',
        'phone',
        'address',
        'bio',
        'date_of_birth'
    ];

    function users()
    {
        return $this->belongsTo(User::class, 'users_id', 'id');
    }
}
