<div>
  @if ($products->isEmpty())
    <p>{{ $emptyText }}</p>
  @elseif ($cardVariant === 'subcat')
    <div class="cat-filters" aria-label="Bộ lọc sản phẩm">
      <div class="cat-filter-left">
        <button type="button" wire:click="toggleDateSort" class="{{ str_starts_with($sort, 'date') ? 'is-active' : '' }}">
          Mới nhất
          <i class="bi {{ $sort === 'date_asc' ? 'bi-chevron-up' : 'bi-chevron-down' }}" aria-hidden="true"></i>
        </button>
        <button type="button" wire:click="togglePriceSort" class="{{ str_starts_with($sort, 'price') ? 'is-active' : '' }}">
          Giá
          <i class="bi {{ $sort === 'price_desc' ? 'bi-chevron-up' : 'bi-chevron-down' }}" aria-hidden="true"></i>
        </button>
      </div>
      <div class="cat-filter-dropdown">
        <button type="button" wire:click="toggleFilterMenu" class="cat-filter-btn {{ $showFilterMenu ? 'is-open' : '' }}">
          <i class="bi bi-funnel" aria-hidden="true"></i>
          {{ $selectedPriceLabel ? 'Lọc: ' . $selectedPriceLabel : 'Lọc' }}
        </button>
        @if ($showFilterMenu)
          <div class="cat-filter-menu">
            <button type="button" wire:click="setPriceRange('all')" class="{{ $price === 'all' ? 'is-active' : '' }}">Tất cả</button>
            @foreach ($priceRanges as $range)
              <button type="button" wire:click="setPriceRange('{{ $range['value'] }}')" class="{{ $price === $range['value'] ? 'is-active' : '' }}">{{ $range['label'] }}</button>
            @endforeach
          </div>
        @endif
      </div>
    </div>
    <div class="subcat-products">
      @foreach ($products as $product)
        <article class="subcat-product-card" wire:key="subcat-product-{{ $product->id }}">
          <a href="{{ route('frontend.product-detail', ['slug' => $product->slug]) }}" class="subcat-product-link">
            <div class="subcat-image-wrap">
              <img src="{{ $product->primary_image_url }}" alt="{{ $product->name }}" loading="lazy" decoding="async" fetchpriority="low" />
              <img class="product-thumb-footer-img" src="{{ asset('footerIMG.webp') }}" alt="" loading="lazy" decoding="async" fetchpriority="low" aria-hidden="true" />
            </div>
            <p class="subcat-price">{{ number_format((float) $product->price, 0, ',', '.') }}đ</p>
            <p class="subcat-name">{{ $product->name }}</p>
            <div class="featured-meta product-compact-meta">
              <span><i class="bi bi-star-fill"></i> {{ number_format((float) $product->rating_avg, 1) }}</span>
              <span>Đã bán {{ number_format((int) $product->sold_count, 0, ',', '.') }}</span>
              <span><i class="bi bi-geo-alt-fill"></i> Buôn Hồ - Đăk Lăk</span>
            </div>
          </a>
        </article>
      @endforeach
    </div>
  @else
    <div class="cat-filters" aria-label="Bộ lọc sản phẩm">
      <div class="cat-filter-left">
        <button type="button" wire:click="toggleDateSort" class="{{ str_starts_with($sort, 'date') ? 'is-active' : '' }}">
          Mới nhất
          <i class="bi {{ $sort === 'date_asc' ? 'bi-chevron-up' : 'bi-chevron-down' }}" aria-hidden="true"></i>
        </button>
        <button type="button" wire:click="togglePriceSort" class="{{ str_starts_with($sort, 'price') ? 'is-active' : '' }}">
          Giá
          <i class="bi {{ $sort === 'price_desc' ? 'bi-chevron-up' : 'bi-chevron-down' }}" aria-hidden="true"></i>
        </button>
      </div>
      <div class="cat-filter-dropdown">
        <button type="button" wire:click="toggleFilterMenu" class="cat-filter-btn {{ $showFilterMenu ? 'is-open' : '' }}">
          <i class="bi bi-funnel" aria-hidden="true"></i>
          {{ $selectedPriceLabel ? 'Lọc: ' . $selectedPriceLabel : 'Lọc' }}
        </button>
        @if ($showFilterMenu)
          <div class="cat-filter-menu">
            <button type="button" wire:click="setPriceRange('all')" class="{{ $price === 'all' ? 'is-active' : '' }}">Tất cả</button>
            @foreach ($priceRanges as $range)
              <button type="button" wire:click="setPriceRange('{{ $range['value'] }}')" class="{{ $price === $range['value'] ? 'is-active' : '' }}">{{ $range['label'] }}</button>
            @endforeach
          </div>
        @endif
      </div>
    </div>
    <div class="cat-products-grid">
      @foreach ($products as $product)
        <article class="cat-product-card" wire:key="cat-product-{{ $product->id }}">
          <a href="{{ route('frontend.product-detail', ['slug' => $product->slug]) }}" class="cat-product-link">
            <div class="cat-image-wrap">
              <img src="{{ $product->primary_image_url }}" alt="{{ $product->name }}" loading="lazy" decoding="async" fetchpriority="low" />
              <img class="product-thumb-footer-img" src="{{ asset('footerIMG.webp') }}" alt="" loading="lazy" decoding="async" fetchpriority="low" aria-hidden="true" />
            </div>
            <p class="cat-price">{{ number_format((float) $product->price, 0, ',', '.') }}đ</p>
            <p class="cat-name">{{ $product->name }}</p>
            <div class="featured-meta product-compact-meta">
              <span><i class="bi bi-star-fill"></i> {{ number_format((float) $product->rating_avg, 1) }}</span>
              <span>Đã bán {{ number_format((int) $product->sold_count, 0, ',', '.') }}</span>
              <span><i class="bi bi-geo-alt-fill"></i> Buôn Hồ - Đăk Lăk</span>
            </div>
          </a>
        </article>
      @endforeach
    </div>
  @endif

  @if ($hasMoreProducts)
    <div class="infinite-loader" wire:intersect.margin.200px="loadMore">
      <span wire:loading.remove wire:target="loadMore">Kéo xuống để tải thêm 20 sản phẩm</span>
      <span wire:loading wire:target="loadMore">Đang tải thêm sản phẩm...</span>
    </div>
  @elseif ($products->isNotEmpty())
  @endif
</div>
