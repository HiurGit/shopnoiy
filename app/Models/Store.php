<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Store extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'name',
        'phone',
        'email',
        'province',
        'district',
        'ward',
        'address_line',
        'open_time',
        'close_time',
        'pickup_enabled',
        'priority_order',
        'status',
    ];
}
