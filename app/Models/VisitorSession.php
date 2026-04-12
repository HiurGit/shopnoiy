<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VisitorSession extends Model
{
    use HasFactory;

    protected $fillable = [
        'visitor_token',
        'session_id',
        'ip_address',
        'route_name',
        'page_type',
        'activity_label',
        'page_title',
        'current_path',
        'current_url',
        'referrer_url',
        'cart_count',
        'cart_value',
        'meta_json',
        'user_agent',
        'first_seen_at',
        'last_seen_at',
    ];

    protected $casts = [
        'meta_json' => 'array',
        'first_seen_at' => 'datetime',
        'last_seen_at' => 'datetime',
    ];
}
