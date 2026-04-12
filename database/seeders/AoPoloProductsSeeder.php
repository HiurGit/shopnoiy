<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class AoPoloProductsSeeder extends Seeder
{
    public function run(): void
    {
        $categoryId = (int) DB::table('categories')->where('slug', 'ao-polo')->value('id');

        if ($categoryId === 0) {
            $this->command?->warn('Khong tim thay danh muc Ao Polo.');

            return;
        }

        $now = now();
        $names = [
            'Ao Polo Hoc Sinh',
            'Ao Polo Tay Dai',
            'Ao Polo Tay Ngan Basic',
            'Ao Polo The Thao',
            'Ao Polo Form Slim',
            'Ao Polo Form Regular',
            'Ao Polo Phoi Co',
            'Ao Polo Phoi Tay',
            'Ao Polo Cotton 4 Chieu',
            'Ao Polo Vai Ca Sau',
            'Ao Polo Cong So',
            'Ao Polo Cao Cap',
            'Ao Polo Don Gian',
            'Ao Polo Tre Trung',
            'Ao Polo Mua He',
            'Ao Polo Thu Dong',
            'Ao Polo Co Khoa',
            'Ao Polo Soc Ngang',
            'Ao Polo Tron Mau',
            'Ao Polo Premium',
        ];

        $colors = DB::table('product_colors')->pluck('id')->map(fn ($id) => (int) $id)->all();
        $sizes = DB::table('product_sizes')->pluck('id')->map(fn ($id) => (int) $id)->all();

        $nextProductId = (int) DB::table('products')->max('id') + 1;
        $nextImageId = (int) DB::table('product_images')->max('id') + 1;
        $nextColorMapId = (int) DB::table('product_color_map')->max('id') + 1;
        $nextSizeMapId = (int) DB::table('product_size_map')->max('id') + 1;

        $products = [];
        $images = [];
        $colorMaps = [];
        $sizeMaps = [];

        foreach ($names as $index => $name) {
            $productId = $nextProductId + $index;
            $slug = Str::slug($name . '-' . $productId);
            $sku = 'POLO-' . str_pad((string) $productId, 6, '0', STR_PAD_LEFT);
            $barcode = '893' . str_pad((string) (200000000 + $productId), 9, '0', STR_PAD_LEFT);

            $products[] = [
                'id' => $productId,
                'category_id' => $categoryId,
                'name' => $name,
                'slug' => $slug,
                'sku' => $sku,
                'barcode' => $barcode,
                'price' => random_int(189000, 459000),
                'stock_qty' => random_int(15, 100),
                'weight_gram' => random_int(220, 380),
                'brand' => 'Shopnoiy',
                'description' => $name . ' voi chat lieu mem mai, de phoi do va mac thoai mai ca ngay.',
                'care_instructions' => 'Giat nhe, khong ngam lau, tranh say nhiet cao.',
                'return_policy' => 'Ho tro doi tra trong 30 ngay.',
                'specs_json' => json_encode([
                    'material' => ['cotton', 'cotton compact', 'poly spandex'][array_rand(['cotton', 'cotton compact', 'poly spandex'])],
                    'fit' => ['regular', 'slim', 'oversize'][array_rand(['regular', 'slim', 'oversize'])],
                    'category' => 'ao-polo',
                ], JSON_UNESCAPED_UNICODE),
                'status' => 'active',
                'is_featured' => $index < 8,
                'view_count' => random_int(500, 12000),
                'rating_avg' => random_int(42, 50) / 10,
                'rating_count' => random_int(15, 220),
                'sold_count' => random_int(50, 1800),
                'created_at' => $now,
                'updated_at' => $now,
            ];

            $images[] = [
                'id' => $nextImageId++,
                'product_id' => $productId,
                'variant_id' => null,
                'image_url' => 'https://picsum.photos/seed/ao-polo-' . $productId . '/900/1100',
                'alt_text' => $name,
                'sort_order' => 1,
                'is_primary' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ];

            $selectedColors = collect($colors)->shuffle()->take(random_int(1, min(3, count($colors))));
            foreach ($selectedColors as $colorId) {
                $colorMaps[] = [
                    'id' => $nextColorMapId++,
                    'product_id' => $productId,
                    'color_id' => (int) $colorId,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }

            $selectedSizes = collect($sizes)->shuffle()->take(random_int(2, min(4, count($sizes))));
            foreach ($selectedSizes as $sizeId) {
                $sizeMaps[] = [
                    'id' => $nextSizeMapId++,
                    'product_id' => $productId,
                    'size_id' => (int) $sizeId,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }
        }

        DB::transaction(function () use ($products, $images, $colorMaps, $sizeMaps): void {
            DB::table('products')->insert($products);
            DB::table('product_images')->insert($images);
            DB::table('product_color_map')->insert($colorMaps);
            DB::table('product_size_map')->insert($sizeMaps);
        });

        $this->command?->info('Da tao 20 san pham mau cho danh muc Ao Polo.');
    }
}
