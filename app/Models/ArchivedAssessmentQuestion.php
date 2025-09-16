<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ArchivedAssessmentQuestion extends Model
{
    use HasFactory;

    protected $table = 'archived_assessment_questions'; // ✅ specify the table

    protected $fillable = [
        'archived_assessment_id',
        'question_text',
        'options',
        'answer_key',
        'sequence_number',
    ];

    protected $casts = [
        'options' => 'array', // ✅ correctly cast options as array
    ];

    public function archivedAssessment()
    {
        return $this->belongsTo(ArchivedAssessment::class, 'archived_assessment_id');
    }
}
