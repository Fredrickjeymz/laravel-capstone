<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Assessment extends Model
{
    protected $fillable = ['teacher_id', 'title', 'subject', 'instructions', 'question_type','rubric','status'];

    public function questions()
    {
        return $this->hasMany(AssessmentQuestion::class);
    }

    public function teacher()
    {
        return $this->belongsTo(Teacher::class);
    }
    
    public function assignedClasses()
    {
        return $this->belongsToMany(SchoolClass::class, 'assessment_class')
                    ->withPivot('time_limit', 'due_date')
                    ->withTimestamps();
    }

    public function studentScores()
    {
        return $this->hasMany(StudentAssessmentScore::class);
    }

}
