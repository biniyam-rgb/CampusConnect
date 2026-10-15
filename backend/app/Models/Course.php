<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Course extends Model
{
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
