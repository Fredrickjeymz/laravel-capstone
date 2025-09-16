<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SavedStudentAssessmentScore extends Model
{
    use HasFactory;

    protected $fillable = [
        'saved_assessment_id',
        'student_id',
        'student_file_path',
        'total_score',
        'max_score',
        'percentage',
        'remarks',
    ];

    public function savedAssessment()
    {
        return $this->belongsTo(SavedAssessment::class);
    }

    public function questionScores()
    {
        return $this->hasMany(SavedStudentAssessmentQuestionScore::class, 'saved_student_assessment_score_id');
    }
}

