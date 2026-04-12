<?php

namespace App\Livewire\Frontend;

use App\Models\Category;
use App\Models\Product;
use App\Support\FrontendProductSearch;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Livewire\Component;

class ProductInfiniteList extends Component
{
    protected $queryString = [
        'sort' => ['except' => 'date_desc'],
        'price' => ['except' => 'all'],
    ];

    public string $scope = 'category';

    public ?int $categoryId = null;

    public string $queryText = '';

    public string $targetSlug = '';

    public ?int $preferredProductId = null;

    public string $fallbackImage = '';

    public string $emptyText = '';

    public string $cardVariant = 'cat';

    public int $perPage = 20;

    public int $visibleCount = 20;

    public int $totalCount = 0;

    public string $sort = 'date_desc';

    public string $price = 'all';

    public bool $showFilterMenu = false;

    public function mount(
        string $scope,
        ?int $categoryId = null,
        string $queryText = '',
        string $targetSlug = '',
        ?int $preferredProductId = null,
        string $fallbackImage = '',
        string $emptyText = '',
        string $cardVariant = 'cat',
        int $perPage = 20
    ): void {
        $this->scope = $scope;
        $this->categoryId = $categoryId;
        $this->queryText = trim($queryText);
        $this->targetSlug = trim($targetSlug);
        $this->preferredProductId = $preferredProductId ? max(1, $preferredProductId) : null;
        $this->fallbackImage = $fallbackImage;
        $this->emptyText = $emptyText;
        $this->cardVariant = $cardVariant;
        $this->perPage = max(1, $perPage);
        $this->visibleCount = $this->perPage;
        $this->normalizeSort();
        $this->normalizePrice();
        $this->totalCount = (clone $this->applyPriceFilter($this->scopedQuery()))->count();
    }

    public function loadMore(): void
    {
        if (! $this->hasMoreProducts()) {
            return;
        }

        $this->visibleCount = min($this->visibleCount + $this->perPage, $this->totalCount);
    }

    public function toggleDateSort(): void
    {
        $this->showFilterMenu = false;

        if ($this->sortField() === 'date') {
            $this->sort = $this->sortDirection() === 'desc' ? 'date_asc' : 'date_desc';
        } else {
            $this->sort = 'date_desc';
        }

        $this->resetVisibleCount();
    }

    public function togglePriceSort(): void
    {
        $this->showFilterMenu = false;

        if ($this->sortField() === 'price') {
            $this->sort = $this->sortDirection() === 'asc' ? 'price_desc' : 'price_asc';
        } else {
            $this->sort = 'price_asc';
        }

        $this->resetVisibleCount();
    }

    public function updatedSort(): void
    {
        $this->normalizeSort();
        $this->resetVisibleCount();
    }

    public function setPriceRange(string $range): void
    {
        $this->price = $this->price === $range ? 'all' : $range;
        $this->normalizePrice();
        $this->showFilterMenu = false;
        $this->resetVisibleCount();
    }

    public function updatedPrice(): void
    {
        $this->normalizePrice();
        $this->resetVisibleCount();
    }

    public function toggleFilterMenu(): void
    {
        $this->showFilterMenu = ! $this->showFilterMenu;
    }

    public function render()
    {
        $scopeQuery = $this->scopedQuery();
        $shouldLoadPriceRanges = $this->showFilterMenu || $this->price !== 'all';
        $priceRanges = $shouldLoadPriceRanges ? $this->availablePriceRanges(clone $scopeQuery) : [];

        if ($this->price !== 'all' && ! collect($priceRanges)->contains(fn (array $range) => $range['value'] === $this->price)) {
            $this->price = 'all';
        }

        $filteredQuery = $this->applyPriceFilter(clone $scopeQuery);
        $this->totalCount = (clone $filteredQuery)->count();
        $baseQuery = $this->applySort(clone $scopeQuery);

        $products = $this->attachPrimaryImage(
            $baseQuery
                ->limit($this->visibleCount)
                ->get(),
            $this->fallbackImage
        );

        return view('livewire.frontend.product-infinite-list', [
            'products' => $products,
            'hasMoreProducts' => $this->hasMoreProducts(),
            'priceRanges' => $priceRanges,
            'selectedPriceLabel' => $this->selectedPriceLabel($priceRanges),
        ]);
    }

    private function hasMoreProducts(): bool
    {
        return $this->visibleCount < $this->totalCount;
    }

    private function scopedQuery(): Builder
    {
        $query = Product::query()->where('status', 'active');
        $this->applyTargetFilter($query);

        if ($this->scope === 'category') {
            if ($this->categoryId) {
                $categoryIds = $this->descendantCategoryIds($this->categoryId);
                $categoryIds[] = $this->categoryId;
                $query->whereIn('category_id', array_values(array_unique($categoryIds)));
            }

            $this->applyCategorySearch($query);

            return $query;
        }

        if ($this->scope === 'subcategories') {
            $productCategoryIds = [];

            if ($this->categoryId) {
                $childCategoryIds = Category::query()
                    ->where('parent_id', $this->categoryId)
                    ->where('status', 'active')
                    ->pluck('id')
                    ->map(fn ($id) => (int) $id)
                    ->all();

                $productCategoryIds = array_values(array_unique([
                    $this->categoryId,
                    ...$childCategoryIds,
                ]));
            }

            if (count($productCategoryIds) === 0) {
                $query->whereRaw('1 = 0');
            } else {
                $query->whereIn('category_id', $productCategoryIds);
            }

            return $query;
        }

        if ($this->scope === 'childcategories') {
            $productCategoryIds = [];

            if ($this->categoryId) {
                $productCategoryIds = $this->descendantCategoryIds($this->categoryId);
                $productCategoryIds[] = $this->categoryId;
                $productCategoryIds = array_values(array_unique($productCategoryIds));
            }

            if (count($productCategoryIds) === 0) {
                $query->whereRaw('1 = 0');
            } else {
                $query->whereIn('category_id', $productCategoryIds);
            }

            return $query;
        }

        if ($this->scope === 'featured') {
            FrontendProductSearch::applyToQuery($query, $this->queryText);

            return $query->where('is_featured', true);
        }

        return $query->whereRaw('1 = 0');
    }

    private function applyTargetFilter(Builder $query): void
    {
        if ($this->targetSlug === '') {
            return;
        }

        $categoryIds = Category::query()
            ->where('status', 'active')
            ->whereHas('target', function ($targetQuery) {
                $targetQuery->where('slug', $this->targetSlug)->where('status', 'active');
            })
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->all();

        if ($categoryIds !== []) {
            $query->whereIn('category_id', $categoryIds);
            return;
        }

        $query->whereRaw('1 = 0');
    }

    private function applyCategorySearch(Builder $query): void
    {
        if (trim($this->queryText) === '') {
            return;
        }

        FrontendProductSearch::applyToQuery($query, $this->queryText);
    }

    private function applySort(Builder $query): Builder
    {
        $query = $this->applyPriceFilter($query);

        if ($this->preferredProductId) {
            $query->orderByRaw('CASE WHEN id = ? THEN 0 ELSE 1 END ASC', [$this->preferredProductId]);
        }

        if ($this->sortField() === 'price') {
            return $query
                ->orderBy('price', $this->sortDirection())
                ->orderBy('id', $this->sortDirection());
        }

        return $query
            ->orderBy('created_at', $this->sortDirection())
            ->orderBy('id', $this->sortDirection());
    }

    private function resetVisibleCount(): void
    {
        $this->visibleCount = $this->perPage;
    }

    private function normalizeSort(): void
    {
        if (! in_array($this->sort, ['date_desc', 'date_asc', 'price_asc', 'price_desc'], true)) {
            $this->sort = 'date_desc';
        }
    }

    private function normalizePrice(): void
    {
        if ($this->price === 'all') {
            return;
        }

        if (! preg_match('/^\d+_\d+$/', $this->price)) {
            $this->price = 'all';
        }
    }

    private function applyPriceFilter(Builder $query): Builder
    {
        if ($this->price === 'all') {
            return $query;
        }

        [$minPrice, $maxPrice] = array_map('intval', explode('_', $this->price, 2));

        return $query
            ->where('price', '>=', $minPrice)
            ->where('price', '<', $maxPrice);
    }

    public function sortField(): string
    {
        return str_starts_with($this->sort, 'price') ? 'price' : 'date';
    }

    public function sortDirection(): string
    {
        return str_ends_with($this->sort, '_asc') ? 'asc' : 'desc';
    }

    private function availablePriceRanges(Builder $query): array
    {
        $bucketSize = 50000;

        $buckets = (clone $query)
            ->selectRaw('FLOOR(price / ?) as bucket_index, COUNT(*) as aggregate', [$bucketSize])
            ->groupBy('bucket_index')
            ->orderBy('bucket_index')
            ->get();

        return $buckets
            ->filter(fn ($bucket) => (int) $bucket->aggregate > 0)
            ->map(function ($bucket) use ($bucketSize) {
                $bucketIndex = (int) $bucket->bucket_index;
                $minPrice = $bucketIndex * $bucketSize;
                $maxPrice = $minPrice + $bucketSize;

                return [
                    'value' => $minPrice . '_' . $maxPrice,
                    'label' => $bucketIndex === 0
                        ? 'Dưới 50k'
                        : number_format($minPrice / 1000, 0, ',', '.') . 'k - ' . number_format($maxPrice / 1000, 0, ',', '.') . 'k',
                ];
            })
            ->values()
            ->all();
    }

    private function selectedPriceLabel(array $priceRanges): ?string
    {
        if ($this->price === 'all') {
            return null;
        }

        if (count($priceRanges) === 0) {
            [$minPrice, $maxPrice] = array_map('intval', explode('_', $this->price, 2));

            return $minPrice === 0
                ? 'Dưới ' . number_format($maxPrice / 1000, 0, ',', '.') . 'k'
                : number_format($minPrice / 1000, 0, ',', '.') . 'k - ' . number_format($maxPrice / 1000, 0, ',', '.') . 'k';
        }

        foreach ($priceRanges as $range) {
            if (($range['value'] ?? null) === $this->price) {
                return $range['label'] ?? null;
            }
        }

        return null;
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
