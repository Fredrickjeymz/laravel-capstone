<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ArchivedAssessment extends Model
{
    protected $fillable = [ 'id', 'teacher_id', 'title', 'subject', 'instructions', 'question_type', 'rubric'];

    public function questions()
    {
        return $this->hasMany(ArchivedAssessmentQuestion::class, 'archived_assessment_id');
    }

    public function teacher()
    {
        return $this->belongsTo(Teacher::class);
    }

    public function archivedteacher()
    {
        return $this->belongsTo(ArchivedTeacher::class);
    }
}

