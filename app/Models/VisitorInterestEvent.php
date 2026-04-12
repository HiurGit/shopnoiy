<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VisitorInterestEvent extends Model
{
    use HasFactory;

    protected $fillable = [
        'visitor_token',
        'event_type',
        'product_id',
        'product_slug',
        'product_name',
        'qty',
        'page_type',
        'source_route',
        'ip_address',
        'user_agent',
        'meta_json',
    ];

    protected $casts = [
        'meta_json' => 'array',
    ];
}
