<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PromoTicker extends Model
{
    use HasFactory;

    protected $fillable = [
        'promotion_id',
        'name',
        'content_text',
        'background_style',
        'text_color',
        'speed_seconds',
        'start_at',
        'end_at',
        'status',
    ];
}
