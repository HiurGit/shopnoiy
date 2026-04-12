<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HomeSectionItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'section_id',
        'item_type',
        'ref_id',
        'title',
        'subtitle',
        'image_url',
        'target_url',
        'sort_order',
        'is_active',
        'start_at',
        'end_at',
        'meta_json',
    ];
}
