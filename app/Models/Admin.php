<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Support\Facades\Hash;

class Admin extends Authenticatable
{


    protected $table = 'admins'; 

    protected $fillable = [
        'full_name', 'email', 'sex', 'username', 'password',
    ];

    protected $hidden = [
        'password',
    ];
}
