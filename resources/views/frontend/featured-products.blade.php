@extends('frontend.layouts.app')

@section('title', 'Sản phẩm thông dụng')

@section('content')
<main class="phone cat-phone">
  @include('frontend.partials.topbar', [
    'headerClass' => 'topbar',
  ])

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
