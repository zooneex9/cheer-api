<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PasswordReset extends Model
{
    protected $fillable = [ 'email', 'token' ];
    //protected $hidden = ['id', 'created_at'];
    //public $timestamps = false;
}
