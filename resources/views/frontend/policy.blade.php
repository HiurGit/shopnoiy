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
    @php
      $isGuidePolicy = request()->routeIs('frontend.policy.guide');
      $rawPolicyContent = trim((string) ($policy['content'] ?? ''));
      $policyLines = preg_split('/\R/u', $rawPolicyContent) ?: [];
      $policyLines = array_values(array_filter(array_map(static fn ($line) => trim((string) $line), $policyLines), static fn ($line) => $line !== ''));

      $policyParagraphs = [];
      $policySteps = [];
      $policySections = [];
      $activeSection = null;

      foreach ($policyLines as $line) {
          if (preg_match('/^(\d+)[\.\)]\s*(.+)$/u', $line, $matches)) {
              if ($activeSection !== null) {
                  $policySections[] = $activeSection;
              }

              $activeSection = [
                  'index' => (int) $matches[1],
                  'title' => trim((string) $matches[2]),
                  'body' => [],
              ];
              continue;
          }

          if (preg_match('/^(?:[-*•])\s*(.+)$/u', $line, $matches)) {
              if ($activeSection !== null) {
                  $activeSection['body'][] = trim((string) $matches[1]);
              } else {
                  $policySteps[] = trim((string) $matches[1]);
              }
              continue;
          }

          if ($activeSection !== null) {
              $activeSection['body'][] = $line;
          } else {
              $policyParagraphs[] = $line;
          }
      }

      if ($activeSection !== null) {
          $policySections[] = $activeSection;
      }

      $introText = $policyParagraphs[0]
          ?? ($policySections[0]['body'][0] ?? 'Website được xây dựng nhằm giúp khách hàng dễ dàng tìm kiếm sản phẩm, lựa chọn sản phẩm và nhận hỗ trợ nhanh chóng.');

      $guideSteps = [];
      if ($policySteps !== []) {
          $guideSteps = $policySteps;
      } elseif ($policySections !== []) {
          foreach ($policySections as $section) {
              $guideSteps[] = $section['title'];
          }
      } else {
          $guideSteps = array_values(array_filter(array_slice($policyParagraphs, 1), static fn ($line) => trim((string) $line) !== ''));
      }
    @endphp

    <article class="policy-page">
      <h1>{{ $policy['title'] }}</h1>

      <nav class="policy-nav" aria-label="Điều hướng chính sách">
        @foreach ($policyLinks as $link)
          <a href="{{ $link['url'] }}" class="policy-nav-link {{ $link['is_active'] ? 'is-active' : '' }}">
            {{ $link['title'] }}
          </a>
        @endforeach
      </nav>

      @if ($isGuidePolicy)
        <section class="policy-content policy-content--guide">
          <article class="policy-guide-card policy-guide-welcome">
            <span class="policy-guide-icon" aria-hidden="true">
              <i class="bi bi-megaphone"></i>
            </span>
            <div class="policy-guide-copy">
              <h2>Chào mừng quý khách đến với {{ $siteName }}</h2>
              <p>Khi sử dụng website, quý khách có thể:</p>
            </div>
          </article>

          <article class="policy-guide-card policy-guide-steps">
        

            <ol class="policy-steps-list">
              @forelse ($guideSteps as $index => $step)
                <li>
                  <span class="policy-step-index">{{ $index + 1 }}</span>
                  <p>{{ $step }}</p>
                </li>
              @empty
                <li>
                  <span class="policy-step-index">1</span>
                  <p>Nội dung hướng dẫn đang được cập nhật.</p>
                </li>
              @endforelse
            </ol>
          </article>

       
        </section>
      @else
        <section class="policy-content policy-content--text">
          @foreach ($policyParagraphs as $paragraph)
            <p>{{ $paragraph }}</p>
          @endforeach

          @if ($policySections !== [])
            <div class="policy-sections">
              @foreach ($policySections as $section)
                <article class="policy-section-card">
                  <div class="policy-section-head">
                    <span class="policy-step-index">{{ $section['index'] }}</span>
                    <strong>{{ $section['title'] }}</strong>
                  </div>

                  @if ($section['body'] !== [])
                    <div class="policy-section-body">
                      @foreach ($section['body'] as $line)
                        <p>{{ $line }}</p>
                      @endforeach
                    </div>
                  @endif
                </article>
              @endforeach
            </div>
          @elseif ($policySteps !== [])
            <ol class="policy-steps-list policy-steps-list--plain">
              @foreach ($policySteps as $index => $step)
                <li>
                  <span class="policy-step-index">{{ $index + 1 }}</span>
                  <p>{{ $step }}</p>
                </li>
              @endforeach
            </ol>
          @endif
        </section>
      @endif
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
