@extends('frontend.layouts.app')

@section('title', 'Không tìm thấy trang')
@section('meta_robots', 'noindex,nofollow')

@section('content')
<main class="phone error404-page">
  <section class="error404-stage">
    <div class="error404-scene">
      <span class="error404-leaf error404-leaf-left"></span>
      <span class="error404-leaf error404-leaf-right"></span>

      <div class="error404-number-row" aria-hidden="true">
        <span class="error404-digit">4</span>

        <div class="error404-zero-wrap">
          <div class="error404-bubble">Lạc đường rồi?</div>
          <div class="error404-zero"></div>
          <span class="error404-zero-shadow"></span>
        </div>

        <span class="error404-digit">4</span>
      </div>

      <h1>404</h1>
      <p>Trang bạn tìm không tồn tại hoặc đã được chuyển đi.</p>
      <a href="{{ route('frontend.home') }}" class="error404-button">Về trang chủ</a>
    </div>
  </section>
</main>
@endsection

@push('head')
<style>
  .error404-page {
    min-height: 100vh;
    background: #fff9ef;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 16px;
    overflow: hidden;
  }

  .error404-stage {
    width: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
  }

  .error404-scene {
    position: relative;
    width: 100%;
    max-width: 390px;
    text-align: center;
    padding: 28px 8px 36px;
  }

  .error404-number-row {
    position: relative;
    display: flex;
    align-items: flex-end;
    justify-content: center;
    gap: 6px;
    margin-bottom: 16px;
  }

  .error404-digit {
    font-size: clamp(126px, 34vw, 170px);
    line-height: 0.82;
    font-weight: 800;
    color: #554621;
    text-shadow:
      0 6px 0 rgba(214, 193, 154, 0.85),
      0 18px 26px rgba(149, 118, 63, 0.18);
  }

  .error404-zero-wrap {
    position: relative;
    width: clamp(100px, 27vw, 126px);
    flex: 0 0 clamp(100px, 27vw, 126px);
    display: flex;
    align-items: center;
    justify-content: center;
  }

  .error404-zero {
    position: relative;
    width: 100%;
    aspect-ratio: 0.7 / 1;
    border-radius: 999px;
    background: #554621;
    box-shadow:
      inset 0 -10px 0 rgba(224, 206, 170, 0.42),
      0 10px 24px rgba(184, 160, 116, 0.16);
    outline: 2px solid rgba(226, 207, 170, 0.72);
  }

  .error404-zero::after {
    content: '';
    position: absolute;
    inset: 18% 25%;
    border-radius: 999px;
    background: linear-gradient(180deg, #fff9ef 0%, #fff5e5 100%);
  }

  .error404-zero-shadow {
    position: absolute;
    left: 50%;
    bottom: -16px;
    width: 76%;
    height: 22px;
    border-radius: 999px;
    background: rgba(188, 163, 118, 0.7);
    filter: blur(2px);
    transform: translateX(-50%);
  }

  .error404-bubble {
    position: absolute;
    top: -12px;
    right: -8px;
    z-index: 3;
    padding: 8px 10px;
    border-radius: 14px;
    background: #ffe99a;
    color: #8a6511;
    font-size: 12px;
    font-weight: 700;
    line-height: 1.2;
    box-shadow: 0 10px 20px rgba(177, 148, 86, 0.18);
  }

  .error404-bubble::after {
    content: '';
    position: absolute;
    left: 16px;
    bottom: -8px;
    border-width: 8px 7px 0;
    border-style: solid;
    border-color: #ffe99a transparent transparent;
  }

  .error404-leaf {
    position: absolute;
    top: 76px;
    width: 68px;
    height: 120px;
    opacity: 0.9;
  }

  .error404-leaf::before,
  .error404-leaf::after {
    content: '';
    position: absolute;
    inset: 0;
    background: radial-gradient(circle at 50% 50%, rgba(121, 209, 178, 0.78) 0%, rgba(121, 209, 178, 0.54) 38%, rgba(121, 209, 178, 0) 70%);
    clip-path: polygon(50% 0%, 64% 18%, 80% 32%, 68% 46%, 82% 62%, 64% 82%, 50% 100%, 36% 82%, 18% 62%, 32% 46%, 20% 32%, 36% 18%);
  }

  .error404-leaf-left {
    left: 8px;
  }

  .error404-leaf-right {
    right: 8px;
  }

  .error404-scene h1 {
    margin: 0 0 8px;
    font-size: 0;
  }

  .error404-scene p {
    margin: 0 0 26px;
    color: #6e6148;
    font-size: 18px;
    line-height: 1.65;
    font-weight: 500;
  }

  .error404-button {
    min-width: 160px;
    min-height: 44px;
    padding: 11px 22px;
    border-radius: 999px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    background: linear-gradient(135deg, #493a17 0%, #554621 100%);
    color: #fff;
    text-decoration: none;
    font-size: 14px;
    font-weight: 800;
    box-shadow: 0 14px 28px rgba(22, 51, 88, 0.16);
    text-transform: uppercase;
    letter-spacing: 0.04em;
  }

  @media (max-width: 360px) {
    .error404-scene p {
      font-size: 17px;
    }

    .error404-bubble {
      right: -2px;
      font-size: 11px;
    }
  }
</style>
@endpush
