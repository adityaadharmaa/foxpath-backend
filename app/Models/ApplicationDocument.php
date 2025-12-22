<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ApplicationDocument extends Model
{
    protected $fillable = [
        'internship_applications_id',
        'type',
        'file_path',
        'original_name',
        'mime_type',
        'size',
        'is_verified',
        'verified_at',
        'verified_by',
        'verification_notes'
    ];
}
