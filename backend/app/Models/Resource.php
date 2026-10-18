<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Course;
use App\Models\User;

class Resource extends Model
{
    protected $fillable = [
        'course_id',
        'uploaded_by',
        'title',
        'file_path',
    ];

    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    public function uploader()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }
}