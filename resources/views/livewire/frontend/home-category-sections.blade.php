<div>
  @foreach ($sections as $section)
    <section class="featured-group" wire:key="home-section-{{ $section->id }}">
      <div class="featured-title-banner">
        <span>{{ mb_strtoupper($section->name) }}</span>
      </div>

      <div class="featured-panel">
        <div class="featured-panel-head">
          <h4>{{ $section->name }}</h4>
          <a href="{{ route('frontend.childcategories', ['slug' => $section->slug]) }}">Xem thêm <i class="bi bi-chevron-right"></i></a>
        </div>

        <div class="featured-products-swiper home-category-products-swiper swiper" wire:ignore>
          <div class="swiper-wrapper">
            @foreach ($section->products as $product)
              <article class="featured-product-card swiper-slide" wire:key="home-section-{{ $section->id }}-product-{{ $product->id }}">
                <a href="{{ route('frontend.product-detail', ['slug' => $product->slug]) }}" class="featured-product-link">
                  <div class="featured-thumb">
                    <img src="{{ $product->primary_image_url }}" alt="{{ $product->name }}" loading="lazy" decoding="async" fetchpriority="low" />
                    <img class="product-thumb-footer-img" src="{{ asset('footerIMG.webp') }}" alt="" loading="lazy" decoding="async" fetchpriority="low" aria-hidden="true" />
                    @if ($product->is_featured)
                      <span class="featured-badge">Nổi bật</span>
                    @endif
                  </div>
                  <h3>{{ $product->name }}</h3>
                  <div class="featured-price-row">
                    <strong>{{ number_format((float) $product->price, 0, ',', '.') }}đ</strong>
                  </div>
                  <div class="featured-meta product-compact-meta">
                    <span><i class="bi bi-star-fill"></i> {{ number_format((float) $product->rating_avg, 1) }}</span>
                    <span>Đã bán {{ number_format((int) $product->sold_count, 0, ',', '.') }}</span>
                    <span><i class="bi bi-geo-alt-fill"></i> Buôn Hồ - Đăk Lăk</span>
                  </div>
                </a>
              </article>
            @endforeach
          </div>
        </div>

        <button class="featured-swiper-nav featured-swiper-prev" type="button" aria-label="Slide trước">
          <i class="bi bi-chevron-left"></i>
        </button>
        <button class="featured-swiper-nav featured-swiper-next" type="button" aria-label="Slide sau">
          <i class="bi bi-chevron-right"></i>
        </button>
      </div>
    </section>
  @endforeach

  @if ($hasMoreSections)
    <div class="infinite-loader" wire:intersect.margin.250px="loadMore">
      <span wire:loading.remove wire:target="loadMore">Kéo xuống để tải thêm 10 danh mục</span>
      <span wire:loading wire:target="loadMore">Đang tải thêm danh mục...</span>
    </div>
  @elseif ($sections->isNotEmpty())
  @endif
</div>
