@extends('frontend.layouts.app')

@section('title', 'Trang chủ')
@section('meta_title', 'Trang chủ')
@section('meta_description', $frontendSiteSlogan)
@section('og_image', $heroBanners->first()->image_url ?? ($frontendMetaOgImage ?? ''))

@push('vendor_styles')
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css" />
@endpush

@section('body_class', $promoTicker ? '' : 'promo-hidden')

@section('content')
<main class="phone">
  @if ($promoTicker)
    <section class="promo-ticker" aria-label="Khuyến mãi nổi bật" style="background: {{ $promoTicker->background_style ?: 'linear-gradient(90deg,#2c3e50,#355c7d,#2c3e50)' }}; color: {{ $promoTicker->text_color ?: '#ffffff' }};">
      <div class="promo-ticker-track">
        <span class="promo-ticker-text">{{ $promoTicker->content_text }}</span>
      </div>
    </section>
  @endif

  @include('frontend.partials.topbar')

  @if ($heroSectionActive)
  <section class="hero-main hero-swiper swiper" aria-label="Banner nổi bật" wire:ignore>
    <div class="swiper-wrapper">
      @forelse ($heroBanners as $banner)
        <a href="{{ $banner->target_url ?: route('frontend.category') }}" class="hero-slide swiper-slide">
          <img
            src="{{ $banner->image_url }}"
            alt="{{ $banner->title ?: 'Banner' }}"
            loading="{{ $loop->first ? 'eager' : 'lazy' }}"
            decoding="async"
            fetchpriority="{{ $loop->first ? 'high' : 'low' }}"
          />
          <div class="hero-overlay">
            @if (!empty($banner->subtitle))
              <p class="hero-eyebrow">{{ $banner->subtitle }}</p>
            @endif
            <h1>{{ $banner->title ?: ' ' }}</h1>
           
          </div>
        </a>
      @empty
        <a href="{{ route('frontend.category') }}" class="hero-slide swiper-slide">
          <img src="https://images.unsplash.com/photo-1617137968427-85924c800a22?auto=format&fit=crop&w=1200&q=80" alt="Banner mặc định" loading="eager" decoding="async" fetchpriority="high" />
          <div class="hero-overlay">
            <h1>Khám phá bộ sưu tập mới</h1>
            <span class="hero-cta">Mua ngay</span>
          </div>
        </a>
      @endforelse
    </div>
    <div class="hero-pagination swiper-pagination" aria-label="Chuyển banner"></div>
  </section>
  @endif

  @if (($femaleCategories ?? collect())->isNotEmpty() || ($maleCategories ?? collect())->isNotEmpty())
  <section class="home-category-cluster">
    @if (($femaleCategories ?? collect())->isNotEmpty())
    <section class="category-section category-section-compact">
      <div class="category-head">
        <h2>Đồ cho Nữ</h2>
        <a href="{{ route('frontend.category', ['target' => 'female']) }}">Xem tất cả sản phẩm Nữ</a>
      </div>
      <div class="category-list category-swiper category-swiper-female swiper" wire:ignore>
        <div class="swiper-wrapper">
          @foreach ($femaleCategories as $category)
            <a href="{{ route('frontend.subcategories', ['slug' => $category->slug]) }}" class="category-item swiper-slide">
              <div class="category-icon">
                <img src="{{ $category->display_image }}" alt="{{ $category->name }}" loading="lazy" decoding="async" fetchpriority="low" />
              </div>
              <p>{{ $category->name }}</p>
            </a>
          @endforeach
        </div>
      </div>
    </section>
    @endif

    @if (($maleCategories ?? collect())->isNotEmpty())
    <section class="category-section category-section-compact">
      <div class="category-head">
        <h2>Đồ cho Nam</h2>
        <a href="{{ route('frontend.category', ['target' => 'male']) }}">Xem tất cả sản phẩm Nam</a>
      </div>
      <div class="category-list category-swiper category-swiper-male swiper" wire:ignore>
        <div class="swiper-wrapper">
          @foreach ($maleCategories as $category)
            <a href="{{ route('frontend.subcategories', ['slug' => $category->slug]) }}" class="category-item swiper-slide">
              <div class="category-icon">
                <img src="{{ $category->display_image }}" alt="{{ $category->name }}" loading="lazy" decoding="async" fetchpriority="low" />
              </div>
              <p>{{ $category->name }}</p>
            </a>
          @endforeach
        </div>
      </div>
    </section>
    @endif
  </section>
  @endif
  @if ($contactSectionActive)
  <section class="contact-section">
    @php
      $contactIconMap = [
        'phone' => 'phone.png',
        'zalo' => 'zalo.png',
        'group' => 'group_zalo.png',
        'guide' => 'guide.png',
      ];
    @endphp
    <div class="contact-head">
      <h2>Cần hỗ trợ liên hệ nha khách yêu ❤️ </h2>
      <span></span>
    </div>
    <div class="contact-list category-list category-swiper contact-swiper swiper" wire:ignore>
      <div class="swiper-wrapper">
        @foreach ($frontendContactLinks as $contactLink)
          <div class="swiper-slide">
            <a
              href="{{ $contactLink['href'] ?: '#' }}"
              class="contact-item is-{{ $contactLink['theme'] }} {{ $contactLink['href'] ? '' : 'is-disabled' }}"
              @if ($contactLink['href'])
                target="_blank" rel="noopener nofollow"
              @else
                aria-disabled="true"
              @endif
            >
              <div class="contact-icon">
                <img
                  src="{{ asset($contactIconMap[$contactLink['theme']] ?? 'phone.png') }}"
                  alt="{{ $contactLink['label'] }}"
                  loading="lazy"
                  decoding="async"
                  fetchpriority="low"
                >
              </div>
              <div class="contact-copy">
                <strong>{{ $contactLink['label'] }}</strong>
              </div>
              <span class="contact-arrow">
                <i class="bi bi-chevron-right"></i>
              </span>
            </a>
          </div>
        @endforeach
        <div class="swiper-slide">
          <a href="{{ route('frontend.customer-ranking') }}" class="contact-item is-ranking">
            <div class="contact-icon">
              <img src="{{ asset('cup.png') }}" alt="Xáº¿p háº¡ng" loading="lazy" decoding="async" fetchpriority="low">
            </div>
            <div class="contact-copy">
              <strong>Xáº¿p háº¡ng</strong>
            </div>
            <span class="contact-arrow">
              <i class="bi bi-chevron-right"></i>
            </span>
          </a>
        </div>
      </div>
    </div>
  </section>
  @endif

  @if ($featuredSectionActive)
  <section class="featured-group home-featured-group">
    <div class="featured-title-banner">
      <span>ĐỒ THÔNG DỤNG</span>
    </div>

    <div class="featured-panel home-featured-panel">
      <div class="featured-panel-head">
        <h4>SẢN PHẨM THÔNG DỤNG </h4>
        <a href="{{ route('frontend.featured-products') }}">Xem thêm <i class="bi bi-chevron-right"></i></a>
      </div>

      <div class="featured-products-swiper primary-featured-swiper swiper" wire:ignore>
        <div class="swiper-wrapper">
          @foreach ($featuredProducts as $product)
            <article class="featured-product-card primary-featured-card swiper-slide">
                <a href="{{ route('frontend.product-detail', ['slug' => $product->slug]) }}" class="featured-product-link">
                  <div class="featured-thumb">
                    <img src="{{ $product->primary_image_url }}" alt="{{ $product->name }}" loading="lazy" decoding="async" fetchpriority="low" />
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
                  <span><i class="bi bi-geo-alt-fill"></i> Buôn Hồ - Đắk Lắk</span>
                </div>
              </a>
            </article>
          @endforeach
        </div>
      </div>

      <button class="featured-swiper-nav featured-swiper-prev primary-featured-prev" type="button" aria-label="Slide trước">
        <i class="bi bi-chevron-left"></i>
      </button>
      <button class="featured-swiper-nav featured-swiper-next primary-featured-next" type="button" aria-label="Slide sau">
        <i class="bi bi-chevron-right"></i>
      </button>
    </div>
  </section>
  @endif

  <livewire:frontend.home-category-sections :per-page="10" />

  <footer class="site-footer">
    <div class="footer-brand">
 
      <span class="footer-brand-text">{{ $siteName }}</span>
    </div>
    <details class="footer-accordion">
        <summary class="footer-Info">GIỚI THIỆU <i class="bi bi-chevron-down"></i></summary>
        <div class="footer-panel footer-info-panel">
            <span class="footer-info-item"><i class="bi bi-shop"></i>{{ $footerInfo['site_name'] }}</span>
            <span class="footer-info-item"><i class="bi bi-stars"></i>{{ $footerInfo['site_slogan'] }}</span>
            <span class="footer-info-item"><i class="bi bi-telephone"></i>Hotline/Zalo: {{ $footerInfo['phone'] }}</span>
            <span class="footer-info-item"><i class="bi bi-envelope"></i>Email: {{ $footerInfo['email'] }}</span>
            @foreach ($footerInfo['stores'] as $store)
              <span class="footer-info-item"><i class="bi bi-geo-alt"></i>{{ $store['name'] }}: {{ $store['address'] }}</span>
            @endforeach
        </div>
      </details>
    <details class="footer-accordion">
      <summary>CHÍNH SÁCH <i class="bi bi-chevron-down"></i></summary>
      <div class="footer-panel">
        <a href="{{ route('frontend.policy.return-warranty') }}">Chính sách đổi trả và bảo hành</a>
        <a href="{{ route('frontend.policy.privacy') }}">Chính sách bảo mật thông tin</a>
        <a href="{{ route('frontend.policy.shipping') }}">Chính sách vận chuyển</a>
        <a href="{{ route('frontend.customer-ranking') }}">Xếp hạng khách hàng</a>
      </div>
    </details>
    @foreach ($footerGroups as $group => $links)
      <details class="footer-accordion">
        <summary>{{ strtoupper($group) }} <i class="bi bi-chevron-down"></i></summary>
        <div class="footer-panel">
          @foreach ($links as $link)
            <a href="{{ $link->url }}">{{ $link->title }}</a>
          @endforeach
        </div>
      </details>
    @endforeach

    <div class="footer-copy">
      <span>Â© {{ date('Y') }} {{ $siteName }}</span>
      <span>Nguyễn Đình Thảo</span>
    </div>
  </footer>

  <button class="scroll-top-btn" type="button" aria-label="Cuộn lên đầu trang">
    <i class="bi bi-arrow-up"></i>
  </button>
</main>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>
<script>
  (() => {
  const topbar = document.querySelector('.topbar');
  const scrollTopBtn = document.querySelector('.scroll-top-btn');
  const resetSwiperElement = (element) => {
    if (!element) {
      return;
    }

    element.classList.remove('swiper-initialized', 'swiper-horizontal', 'swiper-backface-hidden', 'swiper-grid');
    element.removeAttribute('style');

    const wrapper = element.querySelector('.swiper-wrapper');
    if (wrapper) {
      wrapper.removeAttribute('style');
    }

    element.querySelectorAll('.swiper-slide').forEach((slide) => {
      slide.classList.remove(
        'swiper-slide-active',
        'swiper-slide-next',
        'swiper-slide-prev',
        'swiper-slide-visible',
        'swiper-slide-fully-visible'
      );
      slide.removeAttribute('style');
    });
  };

  const reinitializeSwiper = (selector, options) => {
    document.querySelectorAll(selector).forEach((element) => {
      if (element.swiper) {
        element.swiper.destroy(true, true);
      }

      resetSwiperElement(element);
      new Swiper(element, options);
    });
  };

  const syncTopbarState = () => {
    if (topbar) {
      topbar.classList.toggle('scrolled', window.scrollY > 8);
      document.body.classList.toggle('promo-hidden', window.scrollY > 40);
    }
    if (scrollTopBtn) {
      scrollTopBtn.classList.toggle('is-visible', window.scrollY > 320);
    }
  };

  syncTopbarState();
  window.addEventListener('scroll', syncTopbarState, { passive: true });

  if (scrollTopBtn) {
    scrollTopBtn.addEventListener('click', () => {
      window.scrollTo({ top: 0, behavior: 'smooth' });
    });
  }

  const initHomeSwipers = (force = false) => {
    if (!force && document.body.dataset.homeSwipersInitialized === 'true') {
      return;
    }

    reinitializeSwiper('.category-swiper-female', {
      slidesPerView: 4,
      spaceBetween: 6,
      grabCursor: true,
      watchOverflow: true,
      breakpoints: {
        400: {
          slidesPerView: 4.2
        },
        576: {
          slidesPerView: 4.8
        }
      }
    });

    reinitializeSwiper('.category-swiper-male', {
      slidesPerView: 4,
      spaceBetween: 6,
      grabCursor: true,
      watchOverflow: true,
      breakpoints: {
        400: {
          slidesPerView: 4.2
        },
        576: {
          slidesPerView: 4.2
        }
      }
    });

    reinitializeSwiper('.contact-swiper', {
      slidesPerView: 4,
      spaceBetween: 6,
      grabCursor: true,
      watchOverflow: true,
      breakpoints: {
        400: {
          slidesPerView: 4.2
        },
        576: {
          slidesPerView: 4.8
        }
      }
    });

    reinitializeSwiper('.hero-swiper', {
      slidesPerView: 1,
      loop: true,
      speed: 700,
      grabCursor: true,
      autoplay: {
        delay: 3200,
        disableOnInteraction: false
      },
      pagination: {
        el: '.hero-pagination',
        clickable: true
      }
    });

    reinitializeSwiper('.primary-featured-swiper', {
      slidesPerView: 2,
      spaceBetween: 10,
      grid: {
        rows: 2,
        fill: 'row'
      },
      grabCursor: true,
      watchOverflow: true,
      navigation: {
        prevEl: '.primary-featured-prev',
        nextEl: '.primary-featured-next'
      }
    });

    document.body.dataset.homeSwipersInitialized = 'true';
  };

  const initHomeCategorySwipers = (force = false) => {
    document.querySelectorAll('.home-category-products-swiper').forEach((element) => {
      if (!force && element.dataset.swiperInitialized === 'true') {
        return;
      }

      if (element.swiper) {
        element.swiper.destroy(true, true);
      }

      resetSwiperElement(element);

      const panel = element.closest('.featured-panel');
      const prevButton = panel ? panel.querySelector('.featured-swiper-prev') : null;
      const nextButton = panel ? panel.querySelector('.featured-swiper-next') : null;

      new Swiper(element, {
        slidesPerView: 2,
        spaceBetween: 10,
        grabCursor: true,
        watchOverflow: true,
        navigation: prevButton && nextButton ? {
          prevEl: prevButton,
          nextEl: nextButton
        } : undefined
      });

      element.dataset.swiperInitialized = 'true';
    });
  };

  initHomeSwipers();
  initHomeCategorySwipers();

  const homeSectionObserver = new MutationObserver(() => {
    initHomeCategorySwipers();
  });

  homeSectionObserver.observe(document.body, {
    childList: true,
    subtree: true
  });

  window.addEventListener('pageshow', () => {
    syncTopbarState();
    initHomeSwipers(true);
    initHomeCategorySwipers(true);
  });

  document.addEventListener('livewire:navigated', () => {
    syncTopbarState();
    initHomeSwipers(true);
    initHomeCategorySwipers(true);
  });
  })();
</script>
@endpush

