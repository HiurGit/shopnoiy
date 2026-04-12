<?php

namespace App\Livewire\Frontend;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Livewire\Component;

class HomeCategorySections extends Component
{
    public int $perPage = 10;

    public int $visibleCount = 10;

    public int $totalCount = 0;

    public function mount(int $perPage = 10): void
    {
        $this->perPage = max(1, $perPage);
        $this->visibleCount = $this->perPage;
        $this->totalCount = $this->eligibleSections()->count();
    }

    public function loadMore(): void
    {
        if (! $this->hasMoreSections()) {
            return;
        }

        $this->visibleCount = min($this->visibleCount + $this->perPage, $this->totalCount);
    }

    public function render()
    {
        return view('livewire.frontend.home-category-sections', [
            'sections' => $this->eligibleSections()->take($this->visibleCount)->values(),
            'hasMoreSections' => $this->hasMoreSections(),
        ]);
    }

    private function hasMoreSections(): bool
    {
        return $this->visibleCount < $this->totalCount;
    }

    private function eligibleSections(): Collection
    {
        return Category::query()
            ->select('categories.*')
            ->join('categories as parent_categories', 'categories.parent_id', '=', 'parent_categories.id')
            ->whereNull('parent_categories.parent_id')
            ->where('categories.status', 'active')
            ->where('parent_categories.status', 'active')
            ->orderBy('parent_categories.sort_order')
            ->orderBy('parent_categories.name')
            ->orderBy('categories.sort_order')
            ->orderBy('categories.name')
            ->get()
            ->map(function (Category $category) {
                $categoryIds = $this->descendantCategoryIds((int) $category->id);
                $categoryIds[] = (int) $category->id;
                $categoryIds = array_values(array_unique($categoryIds));

                $products = Product::query()
                    ->where('status', 'active')
                    ->whereIn('category_id', $categoryIds)
                    ->orderByDesc('is_featured')
                    ->orderByDesc('sold_count')
                    ->orderByDesc('id')
                    ->limit(7)
                    ->get();

                if ($products->count() < 2) {
                    return null;
                }

                $category->display_image = $this->categoryDisplayImage(
                    $category,
                    'https://images.unsplash.com/photo-1603252109303-2751441dd157?auto=format&fit=crop&w=300&q=80'
                );
                $category->products = $this->attachPrimaryImage(
                    $products,
                    'https://images.unsplash.com/photo-1581044777550-4cfa60707c03?auto=format&fit=crop&w=900&q=80'
                );

                return $category;
            })
            ->filter()
            ->values();
    }

    private function attachPrimaryImage(Collection $products, string $fallback): Collection
    {
        $productIds = $products->pluck('id')->map(fn ($id) => (int) $id)->all();
        $imageByProduct = $this->primaryImagesByProductIds($productIds, $fallback);

        return $products->map(function (Product $product) use ($imageByProduct, $fallback) {
            $product->primary_image_url = $imageByProduct[$product->id] ?? $fallback;

            return $product;
        });
    }

    private function primaryImagesByProductIds(array $productIds, string $fallback): array
    {
        if (count($productIds) === 0) {
            return [];
        }

        $images = DB::table('product_images')
            ->whereIn('product_id', $productIds)
            ->orderByDesc('is_primary')
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();

        $imageByProduct = [];
        foreach ($images as $image) {
            if (! array_key_exists((int) $image->product_id, $imageByProduct)) {
                $imageByProduct[(int) $image->product_id] = $this->resolveImageUrl($image->image_url, $fallback);
            }
        }

        return $imageByProduct;
    }

    private function resolveImageUrl(?string $path, string $fallback): string
    {
        if (empty($path)) {
            return $fallback;
        }

        if (Str::startsWith($path, ['http://', 'https://'])) {
            return $path;
        }

        return url($path);
    }

    private function categoryDisplayImage(Category $category, string $fallback): string
    {
        if (! empty($category->image_url)) {
            return $this->resolveImageUrl($category->image_url, $fallback);
        }

        $categoryIds = $this->descendantCategoryIds((int) $category->id);
        $categoryIds[] = (int) $category->id;
        $randomImage = $this->randomCategoryProductImageUrl(array_values(array_unique($categoryIds)));

        return $randomImage ?: $fallback;
    }

    private function randomCategoryProductImageUrl(array $categoryIds): ?string
    {
        if (count($categoryIds) === 0) {
            return null;
        }

        $imageUrl = DB::table('product_images')
            ->join('products', 'product_images.product_id', '=', 'products.id')
            ->whereIn('products.category_id', $categoryIds)
            ->where('products.status', 'active')
            ->inRandomOrder()
            ->orderByDesc('product_images.is_primary')
            ->orderBy('product_images.sort_order')
            ->orderBy('product_images.id')
            ->value('product_images.image_url');

        return $imageUrl ? $this->resolveImageUrl($imageUrl, '') : null;
    }

    private function descendantCategoryIds(int $categoryId): array
    {
        $descendantIds = [];
        $queue = [$categoryId];

        while (! empty($queue)) {
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
