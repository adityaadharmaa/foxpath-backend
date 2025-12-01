<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    public $timestamps = true;
    protected $table = 'roles';
    protected $fillable = [
        'name',
        'description'
    ];

    function users()
    {
        return $this->hasMany(User::class, 'roles_id', 'id');
    }
}
