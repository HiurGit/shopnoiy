<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class RandomSubcategoryProductsSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();
        $faker = fake('vi_VN');

        $subcategories = Category::query()
            ->whereNotNull('parent_id')
            ->where('status', 'active')
            ->orderBy('id')
            ->get(['id', 'parent_id', 'name', 'slug']);

        if ($subcategories->isEmpty()) {
            $this->command?->warn('Khong tim thay danh muc con nao de tao san pham mau.');

            return;
        }

        $colors = DB::table('product_colors')->pluck('id')->map(fn ($id) => (int) $id)->all();
        $sizes = DB::table('product_sizes')->pluck('id')->map(fn ($id) => (int) $id)->all();

        if (count($colors) === 0 || count($sizes) === 0) {
            $this->command?->warn('Thieu mau sac hoac kich thuoc, khong the tao san pham mau.');

            return;
        }

        $prefixes = ['Premium', 'Basic', 'Daily', 'Soft', 'Active', 'Classic', 'Urban', 'Comfort', 'Flex', 'Essential'];
        $suffixes = ['Fit', 'Style', 'Wear', 'Edition', 'Pro', 'Lite', 'Max', 'Mood', 'Line', 'Form'];
        $materials = ['cotton', 'cotton compact', 'poly spandex', 'modal', 'kaki mem', 'thun lanh'];
        $fits = ['regular', 'slim', 'oversize', 'relaxed'];
        $brands = ['Shopnoiy', 'Noiy Studio', 'Noiy Basics'];

        $nextProductId = (int) DB::table('products')->max('id') + 1;
        $nextImageId = (int) DB::table('product_images')->max('id') + 1;
        $nextColorMapId = (int) DB::table('product_color_map')->max('id') + 1;
        $nextSizeMapId = (int) DB::table('product_size_map')->max('id') + 1;

        $products = [];
        $images = [];
        $colorMaps = [];
        $sizeMaps = [];

        for ($index = 0; $index < 50; $index++) {
            $category = $subcategories->random();
            $prefix = $prefixes[array_rand($prefixes)];
            $suffix = $suffixes[array_rand($suffixes)];
            $productName = trim($category->name . ' ' . $prefix . ' ' . $suffix . ' ' . ($index + 1));
            $slug = Str::slug($productName . '-' . $nextProductId);
            $sku = 'SP-' . str_pad((string) $nextProductId, 6, '0', STR_PAD_LEFT);
            $barcode = '893' . str_pad((string) (100000000 + $nextProductId), 9, '0', STR_PAD_LEFT);
            $price = random_int(159000, 599000);
            $ratingCount = random_int(12, 240);
            $soldCount = random_int(20, 1800);

            $products[] = [
                'id' => $nextProductId,
                'category_id' => (int) $category->id,
                'name' => $productName,
                'slug' => $slug,
                'sku' => $sku,
                'barcode' => $barcode,
                'price' => $price,
                'stock_qty' => random_int(8, 120),
                'weight_gram' => random_int(180, 420),
                'brand' => $brands[array_rand($brands)],
                'description' => $faker->sentence(18),
                'care_instructions' => 'Giat nhe, tranh say nhiet cao.',
                'return_policy' => 'Ho tro doi tra trong 30 ngay.',
                'specs_json' => json_encode([
                    'material' => $materials[array_rand($materials)],
                    'fit' => $fits[array_rand($fits)],
                    'category_slug' => $category->slug,
                ], JSON_UNESCAPED_UNICODE),
                'status' => 'active',
                'is_featured' => random_int(0, 100) <= 35,
                'view_count' => random_int(100, 15000),
                'rating_avg' => random_int(40, 50) / 10,
                'rating_count' => $ratingCount,
                'sold_count' => $soldCount,
                'created_at' => $now,
                'updated_at' => $now,
            ];

            $imageSeed = Str::slug($category->slug . '-' . $nextProductId);
            $images[] = [
                'id' => $nextImageId++,
                'product_id' => $nextProductId,
                'variant_id' => null,
                'image_url' => 'https://picsum.photos/seed/' . $imageSeed . '/900/1100',
                'alt_text' => $productName,
                'sort_order' => 1,
                'is_primary' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ];

            $selectedColors = collect($colors)->shuffle()->take(random_int(1, min(3, count($colors))))->values();
            foreach ($selectedColors as $colorId) {
                $colorMaps[] = [
                    'id' => $nextColorMapId++,
                    'product_id' => $nextProductId,
                    'color_id' => (int) $colorId,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }

            $selectedSizes = collect($sizes)->shuffle()->take(random_int(2, min(4, count($sizes))))->values();
            foreach ($selectedSizes as $sizeId) {
                $sizeMaps[] = [
                    'id' => $nextSizeMapId++,
                    'product_id' => $nextProductId,
                    'size_id' => (int) $sizeId,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }

            $nextProductId++;
        }

        DB::transaction(function () use ($products, $images, $colorMaps, $sizeMaps): void {
            DB::table('products')->insert($products);
            DB::table('product_images')->insert($images);
            DB::table('product_color_map')->insert($colorMaps);
            DB::table('product_size_map')->insert($sizeMaps);
        });

        $this->command?->info('Da tao them 50 san pham mau vao cac danh muc con.');
    }
}
