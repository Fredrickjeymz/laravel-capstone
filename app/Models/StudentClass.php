<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StudentClass extends Model
{
    protected $fillable = [
        'school_class_id',
        'student_id',
        'time_limit',
        'due_date',
    ];

    protected $casts = [
        'due_date' => 'datetime', // Carbon instance automatically
    ];
}
