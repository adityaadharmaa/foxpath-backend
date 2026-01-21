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

    protected $appends = ['profile_picture_url'];

    function users()
    {
        return $this->belongsTo(User::class, 'users_id', 'id');
    }

    public function getProfilePictureUrlAttribute()
    {
        if ($this->profile_picture) {
            return asset('storage/' . $this->profile_picture);
        }
        return null;
    }

    function educations()
    {
        return $this->hasMany(ProfileEducation::class, 'profiles_id', 'id');
    }

    function activeEducation()
    {
        return $this->hasOne(ProfileEducation::class, 'profiles_id', 'id')
        ->where('is_active', true);
    }
}
