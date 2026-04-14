@extends('frontend.layouts.app')

@section('title', $policy['title'])
@section('meta_title', $policy['title'])
@section('meta_description', $policy['description'])

@section('content')
<main class="phone cart-phone policy-phone">
  @include('frontend.partials.topbar', [
    'headerClass' => 'topbar',
  ])

  <section class="cart-subhead">
    <a href="{{ route('frontend.home') }}" class="cart-subhead-back" aria-label="Quay lại" data-history-back="true">
      <i class="bi bi-arrow-left"></i>
    </a>
    <h1>Trang chính sách</h1>
    <span class="cart-subhead-spacer"></span>
  </section>

  <section class="policy-shell">
    <article class="policy-page">
      
      <h1>{{ $policy['title'] }}</h1>
      

  

      <nav class="policy-nav" aria-label="Điều hướng chính sách">
        @foreach ($policyLinks as $link)
          <a href="{{ $link['url'] }}" class="policy-nav-link {{ $link['is_active'] ? 'is-active' : '' }}">
            {{ $link['title'] }}
          </a>
        @endforeach
      </nav>

      <section class="policy-content">
        {!! nl2br(e($policy['content'])) !!}
      </section>

    </article>
  </section>

  <footer class="site-footer">
    <div class="footer-brand">
      <span class="footer-brand-text">{{ $siteName }}</span>
    </div>



    <details class="footer-accordion" open>
      <summary>GIỚI THIỆU <i class="bi bi-chevron-down"></i></summary>
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
    <details class="footer-accordion"  >
      <summary>CHÍNH SÁCH <i class="bi bi-chevron-down"></i></summary>
      <div class="footer-panel">
        @foreach ($policyLinks as $link)
          <a href="{{ $link['url'] }}">{{ $link['title'] }}</a>
        @endforeach
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
      <span>© {{ date('Y') }} {{ $siteName }}</span>
      <span>Nguyễn Đình Thảo</span>
    </div>
  </footer>
</main>
@endsection
