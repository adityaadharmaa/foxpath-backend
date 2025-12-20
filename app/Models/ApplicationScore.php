<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ApplicationScore extends Model
{
    protected $fillable = [
        'internship_applications_id',
        'criterias_id',
        'value',
        'normalized_value',
        'weighted_value'
    ];

    protected $casts = [
        'value' => 'float',
        'narmalized_value' => 'float',
        'weighted_value' => 'float',
    ];

    public function application()
    {
        return $this->belongsTo(InternshipApplication::class, 'internship_applications_id');
    }

    public function criteria()
    {
        return $this->belongsTo(Criteria::class, 'criterias_id');
    }
}
