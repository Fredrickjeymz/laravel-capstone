<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SchoolClass extends Model
{

    protected $fillable = [
        'class_name',
        'subject',
        'section',
        'year_level',
        'teacher_id',
    ];

    public function teacher()
    {
         return $this->belongsTo(Teacher::class, 'teacher_id');
    }

    public function students()
    {
        return $this->belongsToMany(Student::class, 'class_student', 'school_class_id', 'student_id');
    }

    public function assessments()
    {
        return $this->belongsToMany(Assessment::class, 'assessment_class')
                    ->withPivot('time_limit', 'due_date')
                    ->withTimestamps();
    }

}

