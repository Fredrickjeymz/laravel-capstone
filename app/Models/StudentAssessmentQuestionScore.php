<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class StudentAssessmentQuestionScore extends Model
{
        use HasFactory;
    
        protected $fillable = [
            'student_assessment_score_id',
            'assessment_question_id',
            'student_answer',
            'score_given',
            'max_score',
            'criteria_scores', 
        ];
    
        protected $casts = [
            'criteria_scores' => 'array',
        ];
    
        public function studentAssessmentScore()
        {
            return $this->belongsTo(StudentAssessmentScore::class);
        }
    
        public function question()
        {
            return $this->belongsTo(AssessmentQuestion::class, 'assessment_question_id');
        }

    }
    

