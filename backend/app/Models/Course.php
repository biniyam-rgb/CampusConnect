<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use App\Models\Enrollment;
use App\Models\Resource;

class Course extends Model
{
    protected $fillable = [
        'title',
        'course_code',
        'description',
        'lecturer_id',
    ];

    public function lecturer()
    {
        return $this->belongsTo(User::class, 'lecturer_id');
    }

    public function enrollments()
    {
        return $this->hasMany(Enrollment::class);
    }

    public function resources()
    {
        return $this->hasMany(Resource::class);
    }
}
