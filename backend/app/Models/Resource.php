<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Resource extends Model
{
    public function course()
{
    return $this->belongsTo(Course::class);
}

public function uploader()
{
    return $this->belongsTo(User::class, 'uploaded_by');
}
}
