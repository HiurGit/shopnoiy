@extends('frontend.layouts.app')

@section('title', 'Quên mật khẩu')
@section('meta_title', 'Quên mật khẩu khách hàng')
@section('meta_description', 'Giao diện quên mật khẩu dành cho khách hàng tại Shop Nội Y Buôn Hồ.')
@section('meta_robots', 'noindex,nofollow')

@push('head')
  <style>
    body {
      color: #303330;
    }

    .phone.forgot-page,
    .forgot-page {
      min-height: 100vh;
      padding: 72px 16px 48px;
      background: #ffffff;
      font-family: 'Be Vietnam Pro', sans-serif;
    }

    .forgot-shell {
      width: min(100%, 460px);
      margin: 0 auto;
    }

    .forgot-page .topbar {
      margin-left: auto;
      margin-right: auto;
      margin-bottom: 12px;
    }

    .forgot-user-button {
      width: 36px;
      height: 36px;
      display: inline-flex;
      align-items: center;
      justify-content: center;
      border-radius: 14px;
      border: 1px solid rgba(148, 163, 184, 0.28);
      background: linear-gradient(180deg, #ffffff 0%, #f7fafd 100%);
      box-shadow: 0 10px 20px rgba(30, 41, 59, 0.12);
      color: #252c2c;
      text-decoration: none;
      flex-shrink: 0;
    }

    .forgot-page .topbar .forgot-user-icon {
      width: 22px;
      height: 22px;
      display: inline-block;
      color: #252c2c;
    }

    .forgot-card {
      background: rgba(255, 255, 255, 0.88);
    }

    .forgot-brand-hero {
      width: 100%;
      min-height: 140px;
      display: flex;
      align-items: center;
      justify-content: center;
      padding: 30px 24px 4px;
    }

    .forgot-brand-hero img {
      max-width: min(100%, 240px);
      max-height: 120px;
      object-fit: contain;
      display: block;
    }

    .forgot-brand-fallback {
      text-align: center;
      color: #303330;
    }

    .forgot-brand-fallback strong {
      display: block;
      font-size: clamp(1.8rem, 5vw, 2.4rem);
      letter-spacing: -0.03em;
    }

    .forgot-display {
      margin-bottom: 18px;
      text-align: center;
    }

    .forgot-display h1 {
      margin: 0 0 8px;
      font-size: clamp(1.8rem, 5vw, 2.2rem);
      color: #533823;
      letter-spacing: -0.03em;
    }

    .forgot-display p {
      margin: 0;
      color: #6d6f6b;
      font-size: 0.95rem;
      line-height: 1.5;
    }

    .forgot-alert {
      margin-bottom: 16px;
      padding: 14px 16px;
      border-radius: 18px;
      font-size: 0.92rem;
    }

    .forgot-alert-success {
      color: #14532d;
      background: #dcfce7;
      border: 1px solid #bbf7d0;
    }

    .forgot-alert-error {
      color: #991b1b;
      background: #fee2e2;
      border: 1px solid #fecaca;
    }

    .forgot-field {
      margin-bottom: 16px;
    }

    .forgot-label {
      display: block;
      margin-bottom: 8px;
      font-size: 0.78rem;
      letter-spacing: 0.12em;
      color: #5b4632;
      text-transform: uppercase;
    }

    .forgot-input-wrap {
      position: relative;
    }

    .forgot-input {
      width: 100%;
      border: 1px solid #252c2c;
      border-radius: 999px;
      padding: 15px 52px 15px 18px;
      background: #ffffff;
      color: #2f241a;
      font-size: 0.96rem;
      transition: box-shadow 0.2s ease, transform 0.2s ease;
    }

    .forgot-input:focus {
      outline: none;
      box-shadow: 0 0 0 3px rgba(37, 44, 44, 0.12);
      transform: translateY(-1px);
    }

    .forgot-input-icon {
      position: absolute;
      top: 50%;
      right: 18px;
      transform: translateY(-50%);
      color: #8c8174;
      font-size: 1.15rem;
      pointer-events: none;
    }

    .forgot-submit {
      width: 100%;
      border: 0;
      border-radius: 999px;
      padding: 15px 20px;
      background: #252c2c;
      color: #fff;
      font-size: 0.92rem;
      letter-spacing: 0.2em;
      text-transform: uppercase;
    }

    .forgot-submit:hover {
      background: #1d2323;
    }

    .forgot-footer {
      margin-top: 22px;
      text-align: center;
      color: #6d6f6b;
      font-size: 0.92rem;
    }

    .forgot-footer strong {
      display: block;
      margin-bottom: 6px;
      color: #2f241a;
    }

    .forgot-login-link {
      display: inline-flex;
      width: 100%;
      align-items: center;
      justify-content: center;
      margin-top: 12px;
      padding: 15px 20px;
      border: 1px solid #252c2c;
      border-radius: 999px;
      color: #252c2c;
      font-size: 0.92rem;
      text-decoration: none;
      background: transparent;
    }

    .forgot-login-link:hover {
      color: #252c2c;
      background: rgba(37, 44, 44, 0.04);
    }
  </style>
@endpush

@section('content')
  <main class="phone forgot-page">
    <div class="forgot-shell">
      @include('frontend.partials.topbar', [
        'headerClass' => 'topbar',
      ])

      <section class="forgot-card" aria-labelledby="customer-forgot-title">
        <div class="forgot-brand-hero">
          @if (!empty($frontendLogoUrl))
            <img
              src="{{ $frontendLogoUrl }}"
              alt="{{ $frontendSiteName }}"
              loading="eager"
              decoding="async"
            >
          @else
            <div class="forgot-brand-fallback">
              <strong>{{ $frontendSiteName }}</strong>
            </div>
          @endif
        </div>

        <div class="forgot-display">
          <h1 id="customer-forgot-title">Quên mật khẩu</h1>
          <p>Nhập số điện thoại hoặc email để tiếp tục quy trình khôi phục mật khẩu.</p>
        </div>

        @if (session('success'))
          <div class="forgot-alert forgot-alert-success">{{ session('success') }}</div>
        @endif

        @if ($errors->any())
          <div class="forgot-alert forgot-alert-error">{{ $errors->first() }}</div>
        @endif

        <form method="POST" action="{{ route('frontend.password.forgot.submit') }}">
          @csrf

          <div class="forgot-field">
            <label for="login" class="forgot-label">Số điện thoại hoặc email</label>
            <div class="forgot-input-wrap">
              <input
                id="login"
                name="login"
                type="text"
                class="forgot-input"
                placeholder="0901234567 hoặc email@example.com"
                value="{{ old('login') }}"
                autocomplete="username"
                required
              >
              <span class="forgot-input-icon">
                <i class="bi bi-person"></i>
              </span>
            </div>
          </div>

          <button type="submit" class="forgot-submit">Tiếp tục</button>
        </form>

        <div class="forgot-footer">
          <strong>Nhớ lại mật khẩu rồi?</strong>
          <a href="{{ route('frontend.login') }}" class="forgot-login-link">Đăng nhập</a>
        </div>
      </section>
    </div>
  </main>
@endsection
