<?php

namespace App\Console\Commands;

use App\Models\ProductTag;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class NormalizeProductTags extends Command
{
    protected $signature = 'product-tags:normalize';

    protected $description = 'Normalize default product tags to the correct Vietnamese labels and slugs.';

    public function handle(): int
    {
        $tags = [
            1 => 'mặc hằng ngày',
            2 => 'thoải mái',
            3 => 'thoáng khí',
            4 => 'không gọng',
            5 => 'có gọng',
            6 => 'nâng ngực',
            7 => 'tạo khe',
            8 => 'định hình',
            9 => 'gen bụng',
            10 => 'nâng mông',
            11 => 'không lộ viền',
            12 => 'thể thao',
            13 => 'croptop',
            14 => 'mặc váy',
            16 => 'áo dán',
            17 => 'gài trước',
            18 => 'quyến rũ',
            19 => 'ren',
            20 => 'su trơn',
            21 => 'cotton',
            23 => 'tuổi teen',
            24 => 'dễ thương',
            25 => 'giữ ấm',
        ];

        $updated = 0;

        foreach ($tags as $sortOrder => $name) {
            $tag = ProductTag::query()->where('sort_order', $sortOrder)->first();

            if (!$tag) {
                continue;
            }

            $tag->update([
                'name' => $name,
                'slug' => Str::slug($name),
                'status' => 'active',
            ]);

            $updated++;
        }

        $this->info('Đã chuẩn hóa tag sản phẩm: ' . $updated);

        return self::SUCCESS;
    }
}
