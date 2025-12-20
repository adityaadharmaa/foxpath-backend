<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Criteria extends Model
{
    use SoftDeletes;

    public $timestamps = true;
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

    protected $dates = [
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    public function scores()
    {
        return $this->hasMany(ApplicationScore::class, 'criterias_id');
    }
}
