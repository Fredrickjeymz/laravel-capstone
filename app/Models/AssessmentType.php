<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AssessmentType extends Model
{
    protected $fillable = ['typename', 'description'];

    public function questionTypes()
    {
        return $this->hasMany(QuestionType::class, 'assessmenttype_id');
    }
}
