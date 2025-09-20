<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;

class ArchivedTeacher extends Authenticatable
{
    use HasFactory;

    protected $table = 'archived_teachers'; // Important!

    protected $fillable = [ 'id', 'fname', 'mname', 'lname', 'email', 'phone', 'birthdate', 'position', 'gender', 'username', 'password'];
}
