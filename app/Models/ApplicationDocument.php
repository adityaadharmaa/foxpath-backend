<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ApplicationDocument extends Model
{
    protected $fillable = [
        'internship_applications_id',
        'type',
        'status',
        'review_note',
        'file_path',
        'original_name',
        'mime_type',
        'size',
        'reviewed_at',
        'reviewed_by'
    ];

    public function application()
    {
        return $this->belongsTo(
            InternshipApplication::class, 'internship_applications_id', 'id'
        );
    }

    public function reviewer()
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }
}
