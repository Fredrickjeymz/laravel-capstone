<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SavedAssessmentQuestion extends Model
{
    use HasFactory;

    protected $fillable = [
        'saved_assessment_id',
        'question_text',
        'options',
        'answer_key',
        'sequence_number',
    ];

    protected $casts = [
        'options' => 'array',
    ];

    public function savedAssessment()
    {
        return $this->belongsTo(SavedAssessment::class, 'saved_assessment_id');
    }
}
