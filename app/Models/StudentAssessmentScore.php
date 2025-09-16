<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class StudentAssessmentScore extends Model
{

    use HasFactory;

    protected $fillable = [
        'student_id',        // ✅ NEW
        'assessment_id',
        'class_id',
        'total_score',
        'max_score',
        'percentage',
        'remarks',
    ];

    public function assessment()
    {
        return $this->belongsTo(Assessment::class);
    }

    public function questionScores()
    {
        return $this->hasMany(StudentAssessmentQuestionScore::class);
    }

        public function student()
    {
        return $this->belongsTo(Student::class);
    }

}


