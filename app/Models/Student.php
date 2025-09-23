<?php

namespace App\Models;
use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;

class Student extends Authenticatable
{
    use Notifiable;
    
    protected $fillable = [
        'lrn',
        'fname',
        'mname',
        'lname',
        'email',
        'username',
        'password',
        'gender',
        'birthdate',
    ];

    protected $hidden = [
        'password',
    ];

    public function classes()
    {
        return $this->belongsToMany(
            SchoolClass::class,   // related model
            'class_student',      // pivot table name
            'student_id',         // this model’s FK on pivot
            'school_class_id'     // related model’s FK on pivot
        )->withTimestamps();
    }

    // (Optional) full_name accessor…
    public function getFullNameAttribute()
    {
        return collect([$this->fname, $this->mname, $this->lname])
            ->filter()->implode(' ');
    }
}




