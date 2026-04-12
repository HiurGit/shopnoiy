@extends('frontend.layouts.app')

@section('title', $policy['title'])
@section('meta_title', $policy['title'])
@section('meta_description', $policy['description'])

@section('content')
<main class="phone cart-phone policy-phone">
  <header class="cart-topbar">
    @include('frontend.partials.logo')
    <div class="actions">
      <a href="{{ route('frontend.cart') }}" class="bell-wrap" aria-label="Mở giỏ hàng">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true" data-slot="icon" width="20" height="20"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 10.5V6a3.75 3.75 0 1 0-7.5 0v4.5m11.356-1.993 1.263 12c.07.665-.45 1.243-1.119 1.243H4.25a1.125 1.125 0 0 1-1.12-1.243l1.264-12A1.125 1.125 0 0 1 5.513 7.5h12.974c.576 0 1.059.435 1.119 1.007ZM8.625 10.5a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm7.5 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z"></path></svg>
      </a>
    </div>
  </header>

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
