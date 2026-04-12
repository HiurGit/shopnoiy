<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'category_id',
        'name',
        'slug',
        'sku',
        'barcode',
        'price',
        'stock_qty',
        'weight_gram',
        'brand',
        'description',
        'care_instructions',
        'return_policy',
        'specs_json',
        'status',
        'is_featured',
        'view_count',
        'rating_avg',
        'rating_count',
        'sold_count',
    ];

    public function colors()
    {
        return $this->belongsToMany(ProductColor::class, 'product_color_map', 'product_id', 'color_id')->withTimestamps();
    }

    public function sizes()
    {
        return $this->belongsToMany(ProductSize::class, 'product_size_map', 'product_id', 'size_id')->withTimestamps();
    }

    public function tags()
    {
        return $this->belongsToMany(ProductTag::class, 'product_tag_map', 'product_id', 'tag_id')->withTimestamps();
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'category_id');
    }

    protected function name(): Attribute
    {
        return Attribute::make(
            set: fn (mixed $value) => $this->normalizeProductName($value),
        );
    }

    private function normalizeProductName(mixed $value): string
    {
        $name = trim((string) $value);
        $name = preg_replace('/\s+/u', ' ', $name) ?? '';

        if ($name === '') {
            return '';
        }

        return mb_convert_case(mb_strtolower($name, 'UTF-8'), MB_CASE_TITLE, 'UTF-8');
    }
}
