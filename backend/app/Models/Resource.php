<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Resource extends Model
{
    protected $fillable = [
        'course_id',
        'uploaded_by',
        'title',
        'file_path',
    ];

    /**
     * Sensitive attributes that must never be exposed in API responses.
     * The file_path is a private server-side storage path.
     */
    protected $hidden = [
        'file_path',
    ];

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }
}
