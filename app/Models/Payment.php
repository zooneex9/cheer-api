<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{

    protected $fillable = [
        'uuid',
        'detail',
        'amount',
        'voucher',
        'user_id'
    ];

    use HasFactory;
}
