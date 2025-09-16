<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class AssessmentClass extends Model
{
    
    use HasFactory;
    
    protected $table = 'assessment_class';

    protected $fillable = [
        'assessment_id',
        'school_class_id',
        'time_limit',
        'due_date', // ✅ new column
    ];

    protected $casts = [
    'due_date' => 'datetime',
    ];

    public function assessment()
    {
        return $this->belongsTo(Assessment::class);
    }

    public function class()
    {
        return $this->belongsTo(SchoolClass::class, 'school_class_id');
    }
}
