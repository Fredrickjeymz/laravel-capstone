<?php

namespace App\Models;

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SavedStudentAssessmentQuestionScore extends Model
{
    use HasFactory;

    protected $fillable = [
        'saved_student_assessment_score_id',
        'saved_assessment_question_id',
        'student_answer',
        'score_given',
        'max_score',
        'criteria_scores',
        'feedback',
    ];

    protected $casts = [
        'criteria_scores' => 'array',
    ];

    public function savedAssessmentQuestion()
    {
        return $this->belongsTo(SavedAssessmentQuestion::class);
    }

    public function savedStudentAssessmentScore()
    {
        return $this->belongsTo(SavedStudentAssessmentScore::class);
    }
}
