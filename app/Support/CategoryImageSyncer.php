<?php

namespace App\Support;

use App\Models\Category;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class CategoryImageSyncer
{
    public function sync(bool $overwrite = true): array
    {
        $categories = Category::query()
            ->orderByRaw('CASE WHEN parent_id IS NULL THEN 0 ELSE 1 END')
            ->orderBy('parent_id')
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        $directory = UploadPath::absolute('categories');
        if (!is_dir($directory)) {
            File::makeDirectory($directory, 0755, true, true);
        }

        $stats = [
            'updated' => 0,
            'skipped' => 0,
            'missing_source' => 0,
        ];

        foreach ($categories as $category) {
            if (!$overwrite && !empty($category->image_url)) {
                $stats['skipped']++;
                continue;
            }

            $categoryIds = $this->descendantCategoryIds((int) $category->id);
            $categoryIds[] = (int) $category->id;
            $categoryIds = array_values(array_unique($categoryIds));

            $sourceImage = DB::table('product_images')
                ->join('products', 'product_images.product_id', '=', 'products.id')
                ->whereIn('products.category_id', $categoryIds)
                ->where('products.status', 'active')
                ->orderByDesc('products.is_featured')
                ->orderByDesc('products.sold_count')
                ->orderByDesc('product_images.is_primary')
                ->orderBy('product_images.sort_order')
                ->orderByDesc('products.id')
                ->value('product_images.image_url');

            if (!$sourceImage) {
                $stats['missing_source']++;
                continue;
            }

            $sourcePath = public_path(ltrim(str_replace('\\', '/', $sourceImage), '/'));
            if (!is_file($sourcePath)) {
                $stats['missing_source']++;
                continue;
            }

            $extension = strtolower(pathinfo($sourcePath, PATHINFO_EXTENSION) ?: 'jpg');
            $fileName = 'category_' . $category->id . '_' . Str::slug($category->name ?: 'danh-muc') . '.' . $extension;
            $targetPath = $directory . DIRECTORY_SEPARATOR . $fileName;
            File::copy($sourcePath, $targetPath);

            $category->update([
                'image_url' => '/' . trim(UploadPath::relative('categories') . '/' . $fileName, '/'),
            ]);

            $stats['updated']++;
        }

        return $stats;
    }

    private function descendantCategoryIds(int $categoryId): array
    {
        $descendantIds = [];
        $queue = [$categoryId];

        while (!empty($queue)) {
            $children = Category::query()
                ->whereIn('parent_id', $queue)
                ->pluck('id')
                ->map(fn ($id) => (int) $id)
                ->all();

            if (empty($children)) {
                break;
            }

            $descendantIds = array_merge($descendantIds, $children);
            $queue = $children;
        }

        return array_values(array_unique($descendantIds));
    }
}
