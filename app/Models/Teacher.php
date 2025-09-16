<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Teacher extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $table = 'teachers'; 

    protected $fillable = ['id', 'fname', 'mname', 'lname', 'email', 'phone', 'birthdate', 'position', 'gender', 'username', 'password'];

    protected $hidden = ['password', 'remember_token'];

    public function classes()
    {
        return $this->hasMany(SchoolClass::class, 'teacher_id');
    }

}

