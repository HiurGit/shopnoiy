<?php

namespace App\Livewire\Frontend;

use App\Models\Category;
use App\Models\Product;
use App\Support\FrontendProductSearch;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Livewire\Component;

class SearchPage extends Component
{
    public string $mode = 'page';

    public string $initialQuery = '';

    public int $initialPreferredProductId = 0;

    public string $queryText = '';

    public int $preferredProductId = 0;

    public function mount(string $mode = 'page', string $initialQuery = '', int $initialPreferredProductId = 0): void
    {
        $this->mode = $mode;
        $query = trim($initialQuery);

        $this->queryText = $query;
        $this->initialPreferredProductId = max(0, $initialPreferredProductId);
        $this->preferredProductId = $this->initialPreferredProductId;
    }

    public function search(): void
    {
        $this->queryText = trim($this->queryText);

        if ($this->queryText === '') {
            $this->redirectRoute('frontend.search', navigate: true);

            return;
        }

        $this->preferredProductId = 0;
        $this->redirectRoute('frontend.search', ['q' => $this->queryText], navigate: true);
    }

    public function render()
    {
        $topCategories = Category::query()
            ->where('status', 'active')
            ->orderBy('sort_order')
            ->orderBy('name')
            ->limit(16)
            ->get()
            ->map(function (Category $category) {
                $category->display_image = $this->resolveImageUrl(
                    $category->image_url,
                    'https://images.unsplash.com/photo-1603252109303-2751441dd157?auto=format&fit=crop&w=300&q=80'
                );

                return $category;
            });

        $searchPreviewProducts = collect();
        $resultCount = 0;

        if ($this->queryText === '') {
            $searchPreviewProducts = Product::query()
                ->where('status', 'active')
                ->orderByDesc('is_featured')
                ->orderByDesc('sold_count')
                ->limit(24)
                ->get();

            if ($searchPreviewProducts->count() > 6) {
                $searchPreviewProducts = $searchPreviewProducts->shuffle()->take(6)->values();
            }

            $searchPreviewProducts = $this->attachPrimaryImage(
                $searchPreviewProducts,
                'https://images.unsplash.com/photo-1503342217505-b0a15ec3261c?auto=format&fit=crop&w=700&q=80'
            );
        } else {
            $resultsQuery = Product::query()->where('status', 'active');
            FrontendProductSearch::applyToQuery($resultsQuery, $this->queryText);
            $resultCount = (clone $resultsQuery)->count();
        }

        return view('livewire.frontend.search-page', [
            'queryText' => $this->queryText,
            'preferredProductId' => $this->preferredProductId,
            'topCategories' => $topCategories,
            'searchPreviewProducts' => $searchPreviewProducts,
            'resultCount' => $resultCount,
        ]);
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
}
