@extends('frontend.layouts.app')

@section('title', 'Danh mục sản phẩm')
@section('meta_title', 'Danh mục sản phẩm')
@section('meta_description', 'Khám phá toàn bộ danh mục sản phẩm của shop.')
@section('og_image', $topCategories->first()->display_image ?? ($frontendMetaOgImage ?? ''))

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

  <section class="cat-page-panel category-grid-panel">
    @if (($categoryGroups ?? collect())->isNotEmpty())
      <div class="category-group-list">
        @foreach ($categoryGroups as $group)
          <div class="category-group-row">
            <div class="category-grid-4">
              @foreach ($group['items'] as $category)
                <a
                  href="{{ $category->navigation_url }}"
                  class="category-item category-grid-item"
                >
                  <div class="category-icon">
                    <img src="{{ $category->display_image }}" alt="{{ $category->name }}" loading="lazy" decoding="async" fetchpriority="low" />
                  </div>
                  <p>{{ $category->name }}</p>
                </a>
              @endforeach
            </div>
          </div>
        @endforeach
      </div>
    @else
      <p class="category-grid-empty">Chưa có danh mục để hiển thị.</p>
    @endif
  </section>

  <button class="scroll-top-btn" type="button" aria-label="Cuộn lên đầu trang">
    <i class="bi bi-arrow-up"></i>
  </button>
</main>
@endsection

@push('scripts')
<script>
  (() => {
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
  })();
</script>
@endpush
