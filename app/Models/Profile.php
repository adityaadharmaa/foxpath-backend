<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Profile extends Model
{
    protected $fillable = [
        'users_id',
        'applicant_type',
        'full_name',
        'phone',
        'address',
        'bio',
        'profile_picture',
        'date_of_birth'
    ];

    function users()
    {
        return $this->belongsTo(User::class, 'users_id', 'id');
    }
}
