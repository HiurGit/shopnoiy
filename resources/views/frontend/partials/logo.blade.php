<a href="{{ route('frontend.home') }}" class="logo" aria-label="{{ $frontendSiteName }}">
  @if (!empty($frontendLogoUrl))
    <img src="{{ $frontendLogoUrl }}" alt="{{ $frontendSiteName }}" loading="eager" fetchpriority="high" decoding="async">
  @else
    {{ $frontendLogoPrimary }}@if ($frontendLogoAccent)<span>{{ $frontendLogoAccent }}</span>@endif
  @endif
</a>
