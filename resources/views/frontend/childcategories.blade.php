@extends('frontend.layouts.app')

@section('title', 'Danh mục con')

@push('vendor_styles')
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css" />
@endpush

@section('content')
<main class="phone allcat-phone">
  @include('frontend.partials.topbar', [
    'headerClass' => 'topbar',
  ])

  <section class="cart-subhead">
    <a href="{{ $selectedParent ? route('frontend.subcategories', ['slug' => $selectedParent->slug]) : route('frontend.home') }}" class="cart-subhead-back" aria-label="Quay lại" data-history-back="true">
      <i class="bi bi-arrow-left"></i>
    </a>
    <h1>{{ $selectedChild->name }}</h1>
    <span class="cart-subhead-spacer"></span>
  </section>

  <section class="subcat-panel">
    <div class="category-list childcategory-swiper childcategory-list swiper" style="border-radius: 16px;" wire:ignore>
      @if ($siblingChildCategories->isNotEmpty())
        <div class="swiper-wrapper">
          @foreach ($siblingChildCategories as $child)
            <a href="{{ route('frontend.childcategories', ['slug' => $child->slug]) }}" class="category-item swiper-slide {{ $selectedChild->id === $child->id ? 'category-parent is-open' : '' }}">
              <div class="category-icon">
                <img src="{{ $child->display_image }}" alt="{{ $child->name }}" loading="lazy" decoding="async" fetchpriority="low" />
              </div>
              <p>{{ $child->name }}</p>
            </a>
          @endforeach
        </div>
      @endif
    </div>
  </section>

  <section class="cat-page-panel child-products-panel">
    <livewire:frontend.product-infinite-list
      scope="childcategories"
      :category-id="$selectedChild->id"
      fallback-image="https://images.unsplash.com/photo-1503342217505-b0a15ec3261c?auto=format&fit=crop&w=700&q=80"
      empty-text="Danh mục con này chưa có sản phẩm."
      card-variant="cat"
      :per-page="20"
      :key="'child-category-products-' . $selectedChild->id"
    />
  </section>

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

  const initChildcategorySwiper = () => {
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

    document.querySelectorAll('.childcategory-swiper').forEach((element) => {
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

  initChildcategorySwiper();
  window.addEventListener('pageshow', initChildcategorySwiper);
  document.addEventListener('livewire:navigated', initChildcategorySwiper);
</script>
@endpush
