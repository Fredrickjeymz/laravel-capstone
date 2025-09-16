<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SavedAssessment extends Model
{
    use HasFactory;

    protected $fillable = [
        'teacher_id',
        'title',
        'instructions',
        'subject',
        'question_type',
        'rubric',
    ];

    public function questions()
    {
        return $this->hasMany(SavedAssessmentQuestion::class, 'saved_assessment_id');
    }
    public function teacher()
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }

}
