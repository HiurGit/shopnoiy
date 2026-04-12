<?php

namespace App\Support;

use App\Models\Category;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Throwable;
use RuntimeException;

class BasicCatalogImporter
{
    private const COLOR_OPTIONS = [
        ['name' => 'Trắng', 'slug' => 'trang', 'hex_code' => '#FFFFFF', 'sort_order' => 10],
        ['name' => 'Đen', 'slug' => 'den', 'hex_code' => '#000000', 'sort_order' => 20],
        ['name' => 'Da', 'slug' => 'da', 'hex_code' => '#D2A679', 'sort_order' => 30],
        ['name' => 'Đỏ', 'slug' => 'do', 'hex_code' => '#C1121F', 'sort_order' => 40],
        ['name' => 'Nude', 'slug' => 'nude', 'hex_code' => '#E3BC9A', 'sort_order' => 50],
    ];

    private const BRA_SIZES = [
        ['name' => '34', 'slug' => '34', 'sort_order' => 10],
        ['name' => '36', 'slug' => '36', 'sort_order' => 20],
        ['name' => '38', 'slug' => '38', 'sort_order' => 30],
        ['name' => '40', 'slug' => '40', 'sort_order' => 40],
        ['name' => '42', 'slug' => '42', 'sort_order' => 50],
        ['name' => '44', 'slug' => '44', 'sort_order' => 60],
        ['name' => '46', 'slug' => '46', 'sort_order' => 70],
    ];

    private const PANTY_SIZES = [
        ['name' => 'M', 'slug' => 'm', 'sort_order' => 10],
        ['name' => 'L', 'slug' => 'l', 'sort_order' => 20],
        ['name' => 'XL', 'slug' => 'xl', 'sort_order' => 30],
        ['name' => 'XXL', 'slug' => 'xxl', 'sort_order' => 40],
    ];

    private array $slugCounters = [];

    private array $downloadCache = [];

    public function import(string $sourcePath, ?int $limit = null): array
    {
        $items = $this->loadItems($sourcePath);

        if (count($items) === 0) {
            throw new RuntimeException('Khong tim thay du lieu san pham trong file import.');
        }

        if (!is_null($limit)) {
            $items = array_slice($items, 0, max(0, $limit));
        }

        $this->resetCatalogTables();
        $this->resetProductUploadDirectory();

        [$parentIds, $subcategoryIds] = $this->syncCategories($items);
        [$colorIds, $braSizeIds, $pantySizeIds] = $this->syncProductOptions();

        $stats = [
            'products' => 0,
            'images' => 0,
            'categories' => count($parentIds),
            'subcategories' => count($subcategoryIds),
            'image_failures' => [],
        ];

        foreach ($items as $index => $item) {
            $name = trim((string) Arr::get($item, 'name', ''));
            if ($name === '') {
                continue;
            }

            $category = trim((string) Arr::get($item, 'category', ''));
            $subcategory = trim((string) Arr::get($item, 'subcategory', ''));
            $subcategoryKey = $category . '||' . $subcategory;
            $categoryId = $subcategoryIds[$subcategoryKey] ?? ($parentIds[$category] ?? null);

            $productId = DB::table('products')->insertGetId([
                'category_id' => $categoryId,
                'name' => $name,
                'slug' => $this->uniqueProductSlug($name),
                'sku' => sprintf('THU-%05d', $index + 1),
                'barcode' => null,
                'price' => $this->normalizePrice((string) Arr::get($item, 'price', '0')),
                'stock_qty' => 0,
                'weight_gram' => null,
                'brand' => 'Shopnoiy',
                'description' => null,
                'care_instructions' => null,
                'return_policy' => null,
                'specs_json' => json_encode([
                    'source' => 'basic-data-Nhung.js',
                    'category' => $category,
                    'subcategory' => $subcategory,
                ], JSON_UNESCAPED_UNICODE),
                'status' => 'active',
                'is_featured' => 0,
                'view_count' => 0,
                'rating_avg' => $this->normalizeRating(Arr::get($item, 'rating_avg', Arr::get($item, 'RatingTB', 0))),
                'rating_count' => 0,
                'sold_count' => $this->normalizeSoldCount(Arr::get($item, 'sold_count', Arr::get($item, 'Đã bán', 0))),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $this->syncProductColors($productId, $colorIds);
            $this->syncProductSizes($productId, $category, $braSizeIds, $pantySizeIds);

            $stats['products']++;

            $images = Arr::get($item, 'images', []);
            if (!is_array($images)) {
                continue;
            }

            foreach (array_values($images) as $imageIndex => $remoteUrl) {
                $saved = $this->downloadProductImage(
                    $productId,
                    $imageIndex + 1,
                    (string) $remoteUrl
                );

                if ($saved === null) {
                    $stats['image_failures'][] = [
                        'product' => $name,
                        'url' => (string) $remoteUrl,
                    ];
                    continue;
                }

                DB::table('product_images')->insert([
                    'product_id' => $productId,
                    'variant_id' => null,
                    'image_url' => $saved,
                    'alt_text' => $name,
                    'sort_order' => $imageIndex + 1,
                    'is_primary' => $imageIndex === 0 ? 1 : 0,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                $stats['images']++;
            }
        }

        $cacheDir = UploadPath::absolute('products') . DIRECTORY_SEPARATOR . '_cache';
        if (is_dir($cacheDir)) {
            File::deleteDirectory($cacheDir);
        }

        return $stats;
    }

    private function loadItems(string $sourcePath): array
    {
        if (!is_file($sourcePath)) {
            throw new RuntimeException('Khong tim thay file du lieu: ' . $sourcePath);
        }

        $content = File::get($sourcePath);
        $prefix = 'window.APP_DATA = ';

        if (!str_starts_with($content, $prefix)) {
            throw new RuntimeException('File du lieu khong dung dinh dang window.APP_DATA = [...].');
        }

        $json = trim(substr($content, strlen($prefix)));
        $json = rtrim($json, ";\r\n\t ");

        $decoded = json_decode($json, true);

        if (!is_array($decoded)) {
            throw new RuntimeException('Khong giai ma duoc JSON tu file du lieu.');
        }

        return $decoded;
    }

    private function resetCatalogTables(): void
    {
        $tables = [
            'product_images',
            'inventories',
            'product_reviews',
            'product_size_map',
            'product_color_map',
            'order_items',
            'cart_items',
            'products',
            'product_sizes',
            'product_colors',
            'categories',
        ];

        DB::statement('SET FOREIGN_KEY_CHECKS=0');

        try {
            foreach ($tables as $table) {
                if (Schema::hasTable($table)) {
                    DB::table($table)->truncate();
                }
            }
        } finally {
            DB::statement('SET FOREIGN_KEY_CHECKS=1');
        }
    }

    private function resetProductUploadDirectory(): void
    {
        $dir = UploadPath::absolute('products');

        if (is_dir($dir)) {
            File::deleteDirectory($dir);
        }

        File::makeDirectory($dir, 0755, true, true);
    }

    private function syncCategories(array $items): array
    {
        $parentIds = [];
        $subcategoryIds = [];
        $parentSort = 10;

        foreach ($items as $item) {
            $parentName = trim((string) Arr::get($item, 'category', ''));
            $childName = trim((string) Arr::get($item, 'subcategory', ''));

            if ($parentName === '') {
                continue;
            }

            if (!isset($parentIds[$parentName])) {
                $parentIds[$parentName] = Category::query()->insertGetId([
                    'parent_id' => null,
                    'name' => $parentName,
                    'slug' => $this->uniqueCategorySlug($parentName),
                    'icon_class' => null,
                    'image_url' => null,
                    'description' => null,
                    'sort_order' => $parentSort,
                    'status' => 'active',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                $parentSort += 10;
            }

            if ($childName === '') {
                continue;
            }

            $key = $parentName . '||' . $childName;
            if (isset($subcategoryIds[$key])) {
                continue;
            }

            $sortOrder = DB::table('categories')
                ->where('parent_id', $parentIds[$parentName])
                ->max('sort_order');

            $subcategoryIds[$key] = Category::query()->insertGetId([
                'parent_id' => $parentIds[$parentName],
                'name' => $childName,
                'slug' => $this->uniqueCategorySlug($childName),
                'icon_class' => null,
                'image_url' => null,
                'description' => null,
                'sort_order' => ((int) $sortOrder) + 10,
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        return [$parentIds, $subcategoryIds];
    }

    private function uniqueCategorySlug(string $name): string
    {
        $base = Str::slug($name);
        $base = $base !== '' ? $base : 'danh-muc';
        $slug = $base;
        $i = 1;

        while (Category::query()->where('slug', $slug)->exists()) {
            $slug = $base . '-' . $i;
            $i++;
        }

        return $slug;
    }

    private function syncProductOptions(): array
    {
        $colorIds = [];
        foreach (self::COLOR_OPTIONS as $color) {
            $colorIds[] = DB::table('product_colors')->insertGetId([
                'name' => $color['name'],
                'slug' => $color['slug'],
                'hex_code' => $color['hex_code'],
                'sort_order' => $color['sort_order'],
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $braSizeIds = [];
        foreach (self::BRA_SIZES as $size) {
            $braSizeIds[] = DB::table('product_sizes')->insertGetId([
                'name' => $size['name'],
                'slug' => $size['slug'],
                'sort_order' => $size['sort_order'],
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $pantySizeIds = [];
        foreach (self::PANTY_SIZES as $size) {
            $pantySizeIds[] = DB::table('product_sizes')->insertGetId([
                'name' => $size['name'],
                'slug' => $size['slug'],
                'sort_order' => 100 + $size['sort_order'],
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        return [$colorIds, $braSizeIds, $pantySizeIds];
    }

    private function syncProductColors(int $productId, array $colorIds): void
    {
        $rows = array_map(fn (int $colorId) => [
            'product_id' => $productId,
            'color_id' => $colorId,
            'created_at' => now(),
            'updated_at' => now(),
        ], $colorIds);

        if ($rows !== []) {
            DB::table('product_color_map')->insert($rows);
        }
    }

    private function syncProductSizes(int $productId, string $category, array $braSizeIds, array $pantySizeIds): void
    {
        $sizeIds = match ($category) {
            'Áo lót', 'Áo bra' => $braSizeIds,
            'Quần lót' => $pantySizeIds,
            default => [],
        };

        if ($sizeIds === []) {
            return;
        }

        $rows = array_map(fn (int $sizeId) => [
            'product_id' => $productId,
            'size_id' => $sizeId,
            'created_at' => now(),
            'updated_at' => now(),
        ], $sizeIds);

        DB::table('product_size_map')->insert($rows);
    }

    private function uniqueProductSlug(string $name): string
    {
        $base = Str::slug($name);
        $base = $base !== '' ? $base : 'san-pham';

        $count = $this->slugCounters[$base] ?? 0;
        $slug = $count === 0 ? $base : $base . '-' . $count;
        $this->slugCounters[$base] = $count + 1;

        return $slug;
    }

    private function normalizePrice(string $price): float
    {
        $digits = preg_replace('/[^\d]/', '', $price);

        if ($digits === '' || !is_numeric($digits)) {
            return 0;
        }

        return (float) $digits;
    }

    private function normalizeRating(mixed $rating): float
    {
        if (is_string($rating)) {
            $rating = str_replace(',', '.', trim($rating));
        }

        if (!is_numeric($rating)) {
            return 0;
        }

        return max(0, min(5, round((float) $rating, 1)));
    }

    private function normalizeSoldCount(mixed $soldCount): int
    {
        if (is_string($soldCount)) {
            $soldCount = preg_replace('/[^\d]/', '', $soldCount);
        }

        if (!is_numeric($soldCount)) {
            return 0;
        }

        return max(0, (int) $soldCount);
    }

    private function downloadProductImage(int $productId, int $sortOrder, string $url): ?string
    {
        $url = trim($url);
        if ($url === '') {
            return null;
        }

        $uploadDir = UploadPath::absolute('products');
        $productDir = $uploadDir . DIRECTORY_SEPARATOR . 'product_' . $productId;

        if (!is_dir($productDir)) {
            File::makeDirectory($productDir, 0755, true, true);
        }

        $cacheKey = sha1($url);
        $cachePath = $this->downloadCache[$cacheKey]['path'] ?? null;
        $extension = $this->downloadCache[$cacheKey]['extension'] ?? null;

        if (!$cachePath || !$extension || !is_file($cachePath)) {
            try {
                $response = Http::timeout(60)
                    ->retry(2, 500)
                    ->withHeaders([
                        'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/131.0 Safari/537.36',
                        'Referer' => 'https://shopee.vn/',
                    ])
                    ->get($url);
            } catch (Throwable) {
                return null;
            }

            if (!$response->successful()) {
                return null;
            }

            $body = $response->body();
            if ($body === '') {
                return null;
            }

            $extension = $this->detectImageExtension($url, (string) $response->header('Content-Type'));
            $cacheDir = $uploadDir . DIRECTORY_SEPARATOR . '_cache';

            if (!is_dir($cacheDir)) {
                File::makeDirectory($cacheDir, 0755, true, true);
            }

            $cachePath = $cacheDir . DIRECTORY_SEPARATOR . $cacheKey . '.' . $extension;
            File::put($cachePath, $body);

            $this->downloadCache[$cacheKey] = [
                'path' => $cachePath,
                'extension' => $extension,
            ];
        }

        $fileName = sprintf('image_%02d.%s', $sortOrder, $extension);
        $targetPath = $productDir . DIRECTORY_SEPARATOR . $fileName;
        File::copy($cachePath, $targetPath);

        return '/' . trim(UploadPath::relative('products') . '/product_' . $productId . '/' . $fileName, '/');
    }

    private function detectImageExtension(string $url, string $contentType): string
    {
        $contentType = strtolower(trim(strtok($contentType, ';')));

        $map = [
            'image/jpeg' => 'jpg',
            'image/jpg' => 'jpg',
            'image/png' => 'png',
            'image/webp' => 'webp',
            'image/gif' => 'gif',
            'image/bmp' => 'bmp',
        ];

        if (isset($map[$contentType])) {
            return $map[$contentType];
        }

        $path = parse_url($url, PHP_URL_PATH);
        $ext = strtolower(pathinfo((string) $path, PATHINFO_EXTENSION));

        if (in_array($ext, ['jpg', 'jpeg', 'png', 'webp', 'gif', 'bmp'], true)) {
            return $ext === 'jpeg' ? 'jpg' : $ext;
        }

        return 'jpg';
    }
}
