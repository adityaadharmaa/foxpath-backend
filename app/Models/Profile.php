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

    function educations()
    {
        return $this->hasMany(ProfileEducation::class, 'profiles_id');
    }

    function activeEducation()
    {
        return $this->hasOne(ProfileEducation::class, 'profiles_id')
        ->where('is_active', true);
    }
}
