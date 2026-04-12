@extends('frontend.layouts.app')

@section('title', 'Sản phẩm thông dụng')

@section('content')
<main class="phone cat-phone">
  <header class="cat-topbar">
    <div class="topbar-row">
      <a href="{{ route('frontend.home') }}" class="logo">{{ $frontendLogoPrimary }}@if ($frontendLogoAccent) <span>{{ $frontendLogoAccent }}</span>@endif</a>
      <a href="{{ route('frontend.search') }}" class="search-form topbar-search search-entry-link" aria-label="Mở tìm kiếm">
        <i class="bi bi-search search-icon"></i>
        <span class="search-entry-text">Tìm sản phẩm...</span>
        <span class="search-entry-btn">Tìm</span>
      </a>
      <div class="cat-actions">
        <a href="{{ route('frontend.cart') }}" class="cat-bell-wrap" aria-label="Mở giỏ hàng"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true" data-slot="icon" width="20" height="20"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 10.5V6a3.75 3.75 0 1 0-7.5 0v4.5m11.356-1.993 1.263 12c.07.665-.45 1.243-1.119 1.243H4.25a1.125 1.125 0 0 1-1.12-1.243l1.264-12A1.125 1.125 0 0 1 5.513 7.5h12.974c.576 0 1.059.435 1.119 1.007ZM8.625 10.5a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm7.5 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z"></path></svg></a>
        <i class="bi bi-person-circle"></i>
      </div>
    </div>
  </header>

  <section class="cart-subhead">
    <a href="{{ route('frontend.home') }}" class="cart-subhead-back" aria-label="Quay lại" data-history-back="true">
      <i class="bi bi-arrow-left"></i>
    </a>
    <h1>Sản phẩm thông dụng</h1>
    <span class="cart-subhead-spacer"></span>
  </section>

  <section class="cat-page-intro">
    <div class="cat-header-content">
      <div class="cat-header-row">
        <p>Tổng hợp các sản phẩm thông dụng của cửa hàng</p>
      </div>
      <p class="cat-product-count">{{ number_format($featuredProductsTotal, 0, ',', '.') }} sản phẩm</p>
    </div>
  </section>

  <section class="cat-page-panel">
    <livewire:frontend.product-infinite-list
      scope="featured"
      fallback-image="https://images.unsplash.com/photo-1581044777550-4cfa60707c03?auto=format&fit=crop&w=900&q=80"
      empty-text="Chưa có sản phẩm thông dụng nào."
      card-variant="cat"
      :per-page="20"
      :key="'featured-products-page'"
    />
  </section>

  <button class="scroll-top-btn" type="button" aria-label="Cuộn lên đầu trang">
    <i class="bi bi-arrow-up"></i>
  </button>
</main>
@endsection

@push('scripts')
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
</script>
@endpush
