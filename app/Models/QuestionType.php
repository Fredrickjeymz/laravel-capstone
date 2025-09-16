<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class QuestionType extends Model
{
    protected $fillable = ['assessmenttype_id', 'typename', 'description'];

    public function assessmentType()
    {
        return $this->belongsTo(AssessmentType::class, 'assessmenttype_id');
    }

}
