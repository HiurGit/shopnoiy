@extends('frontend.layouts.app')

@section('title', $selectedCategory?->name
  ? $selectedCategory->name
  : ($selectedTarget?->name ? 'Danh mục sản phẩm ' . $selectedTarget->name : 'Danh mục sản phẩm'))
@section('meta_title', $selectedCategory?->name
  ? $selectedCategory->name
  : ($selectedTarget?->name ? 'Danh mục sản phẩm ' . $selectedTarget->name : 'Danh mục sản phẩm'))
@section('meta_description', $selectedCategory?->name
  ? ($showProducts
      ? 'Khám phá ' . number_format($productsTotal, 0, ',', '.') . ' sản phẩm thuộc danh mục ' . $selectedCategory->name . '.'
      : 'Xem các danh mục con thuộc ' . $selectedCategory->name . '.')
  : ($selectedTarget?->name
      ? 'Khám phá toàn bộ sản phẩm dành cho ' . $selectedTarget->name . ' của shop.'
      : 'Khám phá toàn bộ danh mục sản phẩm của shop.'))
@section('og_image', $selectedCategory?->display_image ?? ($topCategories->first()->display_image ?? ($frontendMetaOgImage ?? '')))

@push('vendor_styles')
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css" />
@endpush

@section('content')
<main class="phone cat-phone">
  @include('frontend.partials.topbar', [
    'headerClass' => 'topbar',
    'searchHref' => $queryText !== '' ? route('frontend.search', ['q' => $queryText]) : route('frontend.search'),
    'searchText' => $queryText !== '' ? $queryText : 'Tìm sản phẩm...',
    'searchEntryClass' => $queryText !== '' ? 'has-value' : '',
  ])

  <section class="cart-subhead">
    <a href="{{ route('frontend.home') }}" class="cart-subhead-back" aria-label="Quay lại" data-history-back="true">
      <i class="bi bi-arrow-left"></i>
    </a>
    <h1>Danh mục sản phẩm</h1>
    <span class="cart-subhead-spacer"></span>
  </section>

  <section class="category-section" style="margin-top: 8px;">
    <div class="category-head">
      <h2>{{ $selectedTarget?->name ? 'Danh mục ' . mb_strtolower($selectedTarget->name, 'UTF-8') : 'Danh mục chính' }}</h2>
      <a href="{{ route('frontend.category', $selectedTarget ? ['target' => $selectedTarget->slug] : []) }}">Tất cả</a>
    </div>
    <div class="category-list category-swiper swiper" wire:ignore>
      <div class="swiper-wrapper">
        @foreach ($topCategories as $category)
          <a href="{{ route('frontend.category', array_filter(['slug' => $category->slug, 'target' => $selectedTarget?->slug])) }}" class="category-item swiper-slide {{ $selectedCategory?->id === $category->id ? 'category-parent is-open' : '' }}">
            <div class="category-icon">
              <img src="{{ $category->display_image }}" alt="{{ $category->name }}" loading="lazy" decoding="async" fetchpriority="low" />
            </div>
            <p>{{ $category->name }}</p>
          </a>
        @endforeach
      </div>
    </div>
  </section>

  @if ($showProducts)
    <section class="cat-page-panel">
      <livewire:frontend.product-infinite-list
        scope="category"
        :category-id="$selectedCategory?->id"
        :query-text="$queryText"
        :target-slug="$selectedTarget?->slug ?? ''"
        fallback-image="https://images.unsplash.com/photo-1603252109303-2751441dd157?auto=format&fit=crop&w=700&q=80"
        empty-text="Không tìm thấy sản phẩm phù hợp."
        card-variant="cat"
        :per-page="20"
        :key="'category-products-' . ($selectedCategory?->id ?? 'all') . '-' . ($selectedTarget?->slug ?? 'all') . '-' . md5($queryText)"
      />
    </section>
  @elseif ($selectedCategory)
    <section class="cat-page-panel">
      <div class="category-list subcategory-swiper swiper" wire:ignore>
        @if ($childCategories->isNotEmpty())
          <div class="swiper-wrapper">
            @foreach ($childCategories as $category)
              <a href="{{ route('frontend.category', array_filter(['slug' => $category->slug, 'target' => $selectedTarget?->slug])) }}" class="category-item swiper-slide">
                <div class="category-icon">
                  <img src="{{ $category->display_image }}" alt="{{ $category->name }}" loading="lazy" decoding="async" fetchpriority="low" />
                </div>
                <p>{{ $category->name }}</p>
              </a>
            @endforeach
          </div>
        @else
          <p class="mb-0">Danh mục này chưa có danh mục con.</p>
        @endif
      </div>
    </section>
  @endif

  <button class="scroll-top-btn" type="button" aria-label="Cuộn lên đầu trang">
    <i class="bi bi-arrow-up"></i>
  </button>
</main>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>
<script>
  const scrollTopBtn = document.querySelector('.scroll-top-btn');
  const syncScrollTopButton = () => {
    if (scrollTopBtn) {
      scrollTopBtn.classList.toggle('is-visible', window.scrollY > 320);
    }
  };
  syncScrollTopButton();
  window.addEventListener('scroll', syncScrollTopButton, { passive: true });
  if (scrollTopBtn) {
    scrollTopBtn.addEventListener('click', () => {
      window.scrollTo({ top: 0, behavior: 'smooth' });
    });
  }

  const initCategoryPageSwipers = () => {
    const resetSwiperElement = (element) => {
      element.classList.remove('swiper-initialized', 'swiper-horizontal', 'swiper-backface-hidden');
      element.removeAttribute('style');

      const wrapper = element.querySelector('.swiper-wrapper');
      if (wrapper) {
        wrapper.removeAttribute('style');
      }

      element.querySelectorAll('.swiper-slide').forEach((slide) => {
        slide.classList.remove('swiper-slide-active', 'swiper-slide-next', 'swiper-slide-prev', 'swiper-slide-visible', 'swiper-slide-fully-visible');
        slide.removeAttribute('style');
      });
    };

    document.querySelectorAll('.category-swiper, .subcategory-swiper').forEach((element) => {
      if (element.swiper) {
        element.swiper.destroy(true, true);
      }

      resetSwiperElement(element);

      new Swiper(element, {
        slidesPerView: 3.15,
        spaceBetween: 8,
        grabCursor: true,
        watchOverflow: true,
        observer: true,
        observeParents: true,
        breakpoints: {
          400: {
            slidesPerView: 3.6
          },
          576: {
            slidesPerView: 4
          }
        }
      });
    });
  };

  initCategoryPageSwipers();
  window.addEventListener('pageshow', initCategoryPageSwipers);
  document.addEventListener('livewire:navigated', initCategoryPageSwipers);
</script>
@endpush
