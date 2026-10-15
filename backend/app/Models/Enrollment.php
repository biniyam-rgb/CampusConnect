<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Enrollment extends Model
{
    public function student()
{
    return $this->belongsTo(User::class, 'student_id');
}

public function course()
{
    return $this->belongsTo(Course::class);
}

public function reviewer()
{
    return $this->belongsTo(User::class, 'reviewed_by');
}
}
