<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductColor;
use App\Models\ProductSize;
use App\Models\ProductTag;
use App\Support\UploadPath;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Illuminate\View\View;

class ProductController extends Controller
{
    public function index(): View
    {
        $products = Product::query()
            ->with('category.target:id,name')
            ->orderByDesc('id')
            ->get();
        $primaryImages = DB::table('product_images')
            ->select('product_id', 'image_url')
            ->whereIn('product_id', $products->pluck('id'))
            ->orderByDesc('is_primary')
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get()
            ->groupBy('product_id')
            ->map(fn ($images) => $images->first()->image_url ?? null);

        $products->transform(function (Product $product) use ($primaryImages) {
            $product->primary_image_url = $primaryImages->get($product->id);

            return $product;
        });

        $categories = Category::query()->pluck('name', 'id');

        return view('backend.products.index', compact('products', 'categories'));
    }

    public function quickUpdate(Request $request, Product $product): RedirectResponse|JsonResponse
    {
        $validated = $request->validate([
            'status' => ['required', 'in:active,hidden,draft'],
            'is_featured' => ['nullable', 'boolean'],
        ]);

        $product->update([
            'status' => $validated['status'],
            'is_featured' => $request->boolean('is_featured'),
        ]);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Đã cập nhật nhanh sản phẩm.',
                'product_id' => $product->id,
                'status' => $product->status,
                'is_featured' => (bool) $product->is_featured,
            ]);
        }

        return redirect()->route('backend.products')->with('success', 'Đã cập nhật nhanh sản phẩm.');
    }

    public function create(): View
    {
        $categories = $this->productCategoryOptions();
        $colors = ProductColor::query()->orderBy('sort_order')->orderBy('name')->get();
        $sizes = ProductSize::query()->orderBy('sort_order')->orderBy('name')->get();
        $tags = ProductTag::query()->orderBy('sort_order')->orderBy('name')->get();
        $productImages = collect();
        $colorImageMap = [];

        return view('backend.products.create', compact('categories', 'colors', 'sizes', 'tags', 'productImages', 'colorImageMap'));
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'images' => ['nullable', 'array'],
            'images.*' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp,gif', 'max:5120'],
            'color_image_map' => ['nullable', 'array'],
            'color_image_map.*' => ['nullable', 'string', 'max:500'],
        ]);

        $name = trim((string) $request->input('name', ''));
        if ($name === '') {
            $name = 'Sản phẩm mới';
        }

        $product = Product::create([
            'category_id' => $request->filled('category_id') ? (int) $request->input('category_id') : null,
            'name' => $name,
            'slug' => $this->uniqueSlug((string) $request->input('slug', ''), $name),
            'sku' => $request->input('sku'),
            'barcode' => $request->input('barcode'),
            'price' => (float) $request->input('price', 0),
            'stock_qty' => (int) $request->input('stock_qty', 0),
            'weight_gram' => $request->filled('weight_gram') ? (int) $request->input('weight_gram') : null,
            'brand' => $request->input('brand'),
            'description' => $request->input('description'),
            'care_instructions' => $request->input('care_instructions'),
            'return_policy' => $request->input('return_policy'),
            'specs_json' => $request->filled('specs_json') ? $request->input('specs_json') : null,
            'status' => $request->input('status', 'active'),
            'is_featured' => $request->boolean('is_featured'),
            'view_count' => (int) $request->input('view_count', 0),
            'rating_avg' => (float) $request->input('rating_avg', 0),
            'rating_count' => (int) $request->input('rating_count', 0),
            'sold_count' => (int) $request->input('sold_count', 0),
        ]);

        $colorIds = $this->normalizeIds($request->input('color_ids', []));
        $product->sizes()->sync($this->normalizeIds($request->input('size_ids', [])));
        $product->tags()->sync($this->normalizeIds($request->input('tag_ids', [])));
        $this->storeUploadedImages($request, $product->id);
        $this->ensurePrimaryImage($product->id);
        $product->colors()->sync($this->buildColorSyncPayload($product->id, $colorIds, $request->input('color_image_map', [])));

        return redirect()->route('backend.products')->with('success', 'Đã tạo sản phẩm.');
    }

    public function show(Product $product): View
    {
        $product->load(['colors:id,name', 'sizes:id,name', 'tags:id,name', 'category.target:id,name']);
        $category = $product->category;
        $images = DB::table('product_images')->where('product_id', $product->id)->orderBy('sort_order')->get();

        return view('backend.products.show', compact('product', 'category', 'images'));
    }

    public function edit(Product $product): View
    {
        $product->load(['colors:id', 'sizes:id', 'tags:id']);
        $categories = $this->productCategoryOptions();
        $colors = ProductColor::query()->orderBy('sort_order')->orderBy('name')->get();
        $sizes = ProductSize::query()->orderBy('sort_order')->orderBy('name')->get();
        $tags = ProductTag::query()->orderBy('sort_order')->orderBy('name')->get();
        $productImages = DB::table('product_images')->where('product_id', $product->id)->orderBy('sort_order')->get();
        $colorImageMap = DB::table('product_color_map')
            ->where('product_id', $product->id)
            ->pluck('image_url', 'color_id')
            ->mapWithKeys(fn ($url, $colorId) => [(int) $colorId => trim((string) $url)])
            ->filter(fn ($url) => $url !== '')
            ->all();

        return view('backend.products.edit', compact('product', 'categories', 'colors', 'sizes', 'tags', 'productImages', 'colorImageMap'));
    }

    public function update(Request $request, Product $product): RedirectResponse
    {
        $request->validate([
            'images' => ['nullable', 'array'],
            'images.*' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp,gif', 'max:5120'],
            'delete_image_ids' => ['nullable', 'array'],
            'delete_image_ids.*' => ['integer'],
            'image_sort_order' => ['nullable', 'array'],
            'image_sort_order.*' => ['integer'],
            'color_image_map' => ['nullable', 'array'],
            'color_image_map.*' => ['nullable', 'string', 'max:500'],
        ]);

        $name = trim((string) $request->input('name', $product->name));
        if ($name === '') {
            $name = $product->name;
        }

        $product->update([
            'category_id' => $request->filled('category_id') ? (int) $request->input('category_id') : null,
            'name' => $name,
            'slug' => $this->uniqueSlug((string) $request->input('slug', ''), $name, $product->id),
            'sku' => $request->input('sku'),
            'barcode' => $request->input('barcode'),
            'price' => (float) $request->input('price', $product->price),
            'stock_qty' => (int) $request->input('stock_qty', $product->stock_qty),
            'weight_gram' => $request->filled('weight_gram') ? (int) $request->input('weight_gram') : null,
            'brand' => $request->input('brand'),
            'description' => $request->input('description'),
            'care_instructions' => $request->input('care_instructions'),
            'return_policy' => $request->input('return_policy'),
            'specs_json' => $request->filled('specs_json') ? $request->input('specs_json') : null,
            'status' => $request->input('status', $product->status),
            'is_featured' => $request->boolean('is_featured'),
            'view_count' => (int) $request->input('view_count', $product->view_count),
            'rating_avg' => (float) $request->input('rating_avg', $product->rating_avg),
            'rating_count' => (int) $request->input('rating_count', $product->rating_count),
            'sold_count' => (int) $request->input('sold_count', $product->sold_count),
        ]);

        $colorIds = $this->normalizeIds($request->input('color_ids', []));
        $product->sizes()->sync($this->normalizeIds($request->input('size_ids', [])));
        $product->tags()->sync($this->normalizeIds($request->input('tag_ids', [])));
        $this->deleteSelectedImages($request);
        $this->updateImageSortOrder($request, $product->id);
        $this->storeUploadedImages($request, $product->id);
        $this->ensurePrimaryImage($product->id);
        $product->colors()->sync($this->buildColorSyncPayload($product->id, $colorIds, $request->input('color_image_map', [])));

        return redirect()->route('backend.products')->with('success', 'Đã cập nhật sản phẩm.');
    }

    public function destroy(Request $request, Product $product): RedirectResponse|JsonResponse
    {
        $images = DB::table('product_images')->where('product_id', $product->id)->get();
        foreach ($images as $image) {
            $this->deleteImageFile((string) $image->image_url);
        }

        DB::table('product_images')->where('product_id', $product->id)->delete();
        DB::table('order_items')->where('product_id', $product->id)->delete();
        DB::table('cart_items')->where('product_id', $product->id)->delete();
        $product->delete();

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Đã xóa sản phẩm.',
                'product_id' => $product->id,
            ]);
        }

        return redirect()->route('backend.products')->with('success', 'Đã xóa sản phẩm.');
    }

    private function uniqueSlug(string $rawSlug, string $name, ?int $ignoreId = null): string
    {
        $base = Str::slug($rawSlug !== '' ? $rawSlug : $name);
        $base = $base !== '' ? $base : 'san-pham';
        $slug = $base;
        $i = 1;

        while (
            Product::query()
                ->where('slug', $slug)
                ->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))
                ->exists()
        ) {
            $slug = $base . '-' . $i;
            $i++;
        }

        return $slug;
    }

    private function normalizeIds(mixed $value): array
    {
        if (!is_array($value)) {
            return [];
        }

        return collect($value)
            ->map(fn ($id) => (int) $id)
            ->filter(fn ($id) => $id > 0)
            ->unique()
            ->values()
            ->all();
    }

    private function storeUploadedImages(Request $request, int $productId): void
    {
        $files = $request->file('images', []);
        if (!is_array($files) || count($files) === 0) {
            return;
        }

        $uploadDir = UploadPath::absolute('products');
        if (!is_dir($uploadDir)) {
            File::makeDirectory($uploadDir, 0755, true, true);
        }

        $nextSort = (int) DB::table('product_images')->where('product_id', $productId)->max('sort_order');

        foreach ($files as $file) {
            if (!$file || !$file->isValid()) {
                continue;
            }

            $ext = strtolower($file->extension() ?: 'jpg');
            $fileName = 'product_' . $productId . '_' . Str::uuid()->toString() . '.' . $ext;
            $file->move($uploadDir, $fileName);
            $nextSort++;

            DB::table('product_images')->insert([
                'product_id' => $productId,
                'variant_id' => null,
                'image_url' => '/' . trim(UploadPath::relative('products') . '/' . $fileName, '/'),
                'alt_text' => $request->input('name'),
                'sort_order' => $nextSort,
                'is_primary' => $nextSort === 1 ? 1 : 0,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    private function deleteSelectedImages(Request $request): void
    {
        $deleteIds = $this->normalizeIds($request->input('delete_image_ids', []));
        if (count($deleteIds) === 0) {
            return;
        }

        $images = DB::table('product_images')->whereIn('id', $deleteIds)->get();
        foreach ($images as $image) {
            $this->deleteImageFile((string) $image->image_url);
        }

        DB::table('product_images')->whereIn('id', $deleteIds)->delete();
    }

    private function updateImageSortOrder(Request $request, int $productId): void
    {
        $sortOrderInput = $request->input('image_sort_order', []);
        if (!is_array($sortOrderInput) || count($sortOrderInput) === 0) {
            return;
        }

        $existingIds = DB::table('product_images')
            ->where('product_id', $productId)
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->all();

        if (count($existingIds) === 0) {
            return;
        }

        $normalizedIds = collect($sortOrderInput)
            ->map(fn ($id) => (int) $id)
            ->filter(fn ($id) => in_array($id, $existingIds, true))
            ->unique()
            ->values()
            ->all();

        if (count($normalizedIds) === 0) {
            return;
        }

        DB::transaction(function () use ($productId, $existingIds, $normalizedIds): void {
            DB::table('product_images')
                ->where('product_id', $productId)
                ->update([
                    'is_primary' => 0,
                    'updated_at' => now(),
                ]);

            $orderedIds = array_values(array_unique(array_merge(
                $normalizedIds,
                array_values(array_diff($existingIds, $normalizedIds))
            )));

            foreach ($orderedIds as $index => $imageId) {
                DB::table('product_images')
                    ->where('id', $imageId)
                    ->where('product_id', $productId)
                    ->update([
                        'sort_order' => $index + 1,
                        'is_primary' => $index === 0 ? 1 : 0,
                        'updated_at' => now(),
                    ]);
            }
        });
    }

    private function deleteImageFile(string $imageUrl): void
    {
        if ($imageUrl === '') {
            return;
        }

        $relative = ltrim(str_replace('\\', '/', $imageUrl), '/');
        $fullPath = public_path($relative);
        if (is_file($fullPath)) {
            @unlink($fullPath);
        }
    }

    private function ensurePrimaryImage(int $productId): void
    {
        $hasPrimary = DB::table('product_images')
            ->where('product_id', $productId)
            ->where('is_primary', 1)
            ->exists();

        if ($hasPrimary) {
            return;
        }

        $firstImageId = DB::table('product_images')
            ->where('product_id', $productId)
            ->orderBy('sort_order')
            ->orderBy('id')
            ->value('id');

        if (!$firstImageId) {
            return;
        }

        DB::table('product_images')->where('id', $firstImageId)->update(['is_primary' => 1]);
    }

    private function buildColorSyncPayload(int $productId, array $colorIds, mixed $colorImageMapInput): array
    {
        if (count($colorIds) === 0) {
            return [];
        }

        $requestedMap = is_array($colorImageMapInput) ? $colorImageMapInput : [];
        $allowedImageUrls = DB::table('product_images')
            ->where('product_id', $productId)
            ->pluck('image_url')
            ->map(fn ($url) => trim((string) $url))
            ->filter(fn ($url) => $url !== '')
            ->all();

        $allowedLookup = array_fill_keys($allowedImageUrls, true);
        $payload = [];

        foreach ($colorIds as $colorId) {
            $rawValue = trim((string) ($requestedMap[$colorId] ?? ''));
            $payload[$colorId] = [
                'image_url' => ($rawValue !== '' && isset($allowedLookup[$rawValue])) ? $rawValue : null,
            ];
        }

        return $payload;
    }

    private function productCategoryOptions()
    {
        $categories = Category::query()
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        $categoriesByParent = $categories->groupBy(fn (Category $category) => $category->parent_id ?? 0);

        return $this->flattenProductCategories($categoriesByParent);
    }

    private function flattenProductCategories($categoriesByParent, int $parentId = 0, int $depth = 0)
    {
        return collect($categoriesByParent->get($parentId, []))
            ->flatMap(function (Category $category) use ($categoriesByParent, $depth) {
                $category->display_name = str_repeat('---- ', $depth) . $category->name;

                return collect([$category])->concat(
                    $this->flattenProductCategories($categoriesByParent, (int) $category->id, $depth + 1)
                );
            })
            ->values();
    }
}
