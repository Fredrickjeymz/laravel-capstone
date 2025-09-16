<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AssessmentQuestion extends Model
{
    protected $fillable = ['assessment_id', 'question_text', 'options', 'answer_key', 'sequence_number'];

    protected $casts = [
        'options' => 'array', // Cast JSON to array
    ];

    public function assessment()
    {
        return $this->belongsTo(Assessment::class);
    }
}
