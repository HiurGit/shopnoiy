@if ($mode === 'overlay')
  <div class="search-overlay" data-search-overlay hidden>
    <div class="search-overlay-backdrop" data-search-overlay-close></div>
    <main class="phone search-page search-overlay-panel">
@else
  <main class="phone search-page">
@endif
  <header class="search-page-header">
    <div class="search-page-bar">
      @if ($mode === 'overlay')
        <button type="button" class="search-page-back" aria-label="Đóng tìm kiếm" data-search-overlay-close>
          <i class="bi bi-arrow-left"></i>
        </button>
      @else
      <a href="{{ url()->previous() }}" class="search-page-back" aria-label="Quay lại" data-search-page-back="true">
        <i class="bi bi-arrow-left"></i>
      </a>
      @endif
      <form
        action="{{ route('frontend.search') }}"
        class="search-form search-page-form"
        method="GET"
        wire:submit="search"
        data-search-history-form="true"
        data-livewire-submit="true"
        data-search-autocomplete="{{ route('frontend.search-suggestions') }}"
      >
        <i class="bi bi-search search-icon"></i>
        <input
          type="search"
          name="q"
          wire:model="queryText"
          placeholder="Tìm sản phẩm, ví dụ: áo lót không gọng"
          aria-label="Tìm sản phẩm"
          autofocus
        />
        <button type="submit" class="search-submit-btn">Tìm</button>
      </form>
    </div>
  </header>

  @if ($queryText === '')
  <section class="search-chip-section">
    <div class="search-section-head">
      <h2>Tìm kiếm nổi bật</h2>
    </div>
    <div class="search-chip-list">
      @foreach ($topCategories as $category)
        <a href="{{ $category->parent_id ? route('frontend.childcategories', ['slug' => $category->slug]) : route('frontend.subcategories', ['slug' => $category->slug]) }}" class="search-chip">{{ $category->name }}</a>
      @endforeach
    </div>
  </section>
  @endif

  <section class="search-chip-section">
    <div class="search-section-head">
      <h2>Tìm gần đây</h2>
      <button class="search-clear-history" type="button">Xóa</button>
    </div>
    <div class="search-chip-list" data-search-history></div>
  </section>

  @if ($queryText !== '')
    <section class="search-results-head">
      <div>
        <p class="search-results-label">Kết quả tìm kiếm</p>
       
      </div>
    </section>

    <section class="cat-page-panel search-results-panel">
      <livewire:frontend.product-infinite-list
        scope="category"
        :query-text="$queryText"
        :preferred-product-id="$preferredProductId ?? 0"
        fallback-image="https://images.unsplash.com/photo-1503342217505-b0a15ec3261c?auto=format&fit=crop&w=700&q=80"
        empty-text="Không tìm thấy sản phẩm phù hợp."
        card-variant="cat"
        :per-page="20"
        :key="'search-results-' . md5($queryText)"
      />
    </section>
  @else
    <section class="featured-group search-preview-group">
      <div class="featured-panel">
        <div class="featured-panel-head">
          <h4>Gợi ý cho bạn</h4>
        </div>

        <div class="search-preview-grid">
          @foreach ($searchPreviewProducts as $product)
            <article class="featured-product-card">
              <a href="{{ route('frontend.product-detail', ['slug' => $product->slug]) }}" class="featured-product-link">
                <div class="featured-thumb">
                  <img src="{{ $product->primary_image_url }}" alt="{{ $product->name }}" loading="lazy" decoding="async" fetchpriority="low" />
                  <img class="product-thumb-footer-img" src="{{ asset('footerIMG.webp') }}" alt="" loading="lazy" decoding="async" fetchpriority="low" aria-hidden="true" />
                </div>
                <h3>{{ $product->name }}</h3>
                <div class="featured-price-row">
                  <strong>{{ number_format((float) $product->price, 0, ',', '.') }}đ</strong>
                </div>
              </a>
            </article>
          @endforeach
        </div>
      </div>
    </section>
  @endif
@if ($mode === 'overlay')
    </main>
  </div>
@else
  </main>
@endif
