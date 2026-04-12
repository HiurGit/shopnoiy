<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Promotion extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'promotion_type',
        'channel',
        'discount_type',
        'discount_value',
        'min_order_value',
        'max_discount_value',
        'start_at',
        'end_at',
        'status',
        'description',
    ];
}
