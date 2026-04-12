@extends('backend.layouts.app')

@section('title', 'Chi tiet san pham')

@section('content')
<style>
  .product-gallery-shell {
    display: grid;
    gap: 16px;
  }
  .product-gallery-stage {
    position: relative;
    min-height: 420px;
    border-radius: 20px;
    border: 1px solid #dbe3ec;
    background: linear-gradient(180deg, #fcfdff 0%, #f4f7fb 100%);
    display: flex;
    align-items: center;
    justify-content: center;
    overflow: hidden;
    padding: 18px;
  }
  .product-gallery-stage img {
    max-width: 100%;
    max-height: 380px;
    object-fit: contain;
    border-radius: 14px;
    cursor: zoom-in;
  }
  .product-gallery-empty {
    color: #94a3b8;
    font-size: .95rem;
  }
  .product-gallery-nav {
    position: absolute;
    top: 50%;
    transform: translateY(-50%);
    width: 42px;
    height: 42px;
    border: 0;
    border-radius: 999px;
    background: rgba(255, 255, 255, .95);
    box-shadow: 0 12px 24px rgba(15, 23, 42, .12);
    color: #1e293b;
    display: inline-flex;
    align-items: center;
    justify-content: center;
  }
  .product-gallery-nav.prev { left: 14px; }
  .product-gallery-nav.next { right: 14px; }
  .product-gallery-strip {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(88px, 1fr));
    gap: 12px;
  }
  .product-gallery-thumb {
    width: 100%;
    aspect-ratio: 1 / 1;
    object-fit: cover;
    border-radius: 14px;
    border: 2px solid transparent;
    background: #fff;
    cursor: pointer;
    transition: transform .18s ease, border-color .18s ease, box-shadow .18s ease;
  }
  .product-gallery-thumb:hover {
    transform: translateY(-2px);
    box-shadow: 0 12px 20px rgba(15, 23, 42, .1);
  }
  .product-gallery-thumb.is-active {
    border-color: #1d4ed8;
    box-shadow: 0 0 0 3px rgba(37, 99, 235, .16);
  }
  .product-gallery-meta {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 12px;
    color: #64748b;
    font-size: .92rem;
  }
  .product-gallery-modal[hidden] {
    display: none !important;
  }
  .product-gallery-modal {
    position: fixed;
    inset: 0;
    z-index: 2200;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 24px;
  }
  .product-gallery-modal-backdrop {
    position: absolute;
    inset: 0;
    background: rgba(2, 6, 23, .82);
    backdrop-filter: blur(4px);
  }
  .product-gallery-modal-dialog {
    position: relative;
    width: min(94vw, 1100px);
    border-radius: 22px;
    background: #0f172a;
    overflow: hidden;
    box-shadow: 0 30px 80px rgba(2, 6, 23, .42);
  }
  .product-gallery-modal-head {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 12px;
    padding: 14px 18px;
    color: #fff;
    background: rgba(255, 255, 255, .04);
  }
  .product-gallery-modal-head h3 {
    margin: 0;
    font-size: 1rem;
    font-weight: 700;
  }
  .product-gallery-modal-close {
    border: 0;
    background: transparent;
    color: #fff;
    font-size: 1.2rem;
  }
  .product-gallery-modal-body {
    position: relative;
    min-height: 70vh;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 20px;
  }
  .product-gallery-modal-body img {
    max-width: 100%;
    max-height: calc(94vh - 130px);
    object-fit: contain;
  }
  .product-gallery-modal-nav {
    position: absolute;
    top: 50%;
    transform: translateY(-50%);
    width: 48px;
    height: 48px;
    border: 0;
    border-radius: 999px;
    background: rgba(255, 255, 255, .12);
    color: #fff;
    display: inline-flex;
    align-items: center;
    justify-content: center;
  }
  .product-gallery-modal-nav.prev { left: 18px; }
  .product-gallery-modal-nav.next { right: 18px; }
  @media (max-width: 767.98px) {
    .product-gallery-stage {
      min-height: 280px;
    }
    .product-gallery-stage img {
      max-height: 240px;
    }
    .product-gallery-strip {
      grid-template-columns: repeat(4, minmax(0, 1fr));
    }
  }
</style>

<div class="app-content-header">
  <div class="container-fluid d-flex justify-content-between align-items-center">
    <h1 class="mb-0">Chi tiet san pham</h1>
    <div class="d-flex gap-2">
      <a href="{{ route('backend.products.edit', $product) }}" class="btn btn-outline-primary btn-sm">Sua</a>
      <a href="{{ route('backend.products') }}" class="btn btn-secondary btn-sm">Quay lai</a>
    </div>
  </div>
</div>
<div class="app-content">
  <div class="container-fluid">
    <div class="card">
      <div class="card-body">
        <table class="table table-bordered">
          <tbody>
            <tr><th style="width:220px;">ID</th><td>{{ $product->id }}</td></tr>
            <tr><th>Ten</th><td>{{ $product->name }}</td></tr>
            <tr><th>Slug</th><td>{{ $product->slug }}</td></tr>
            <tr><th>Danh muc</th><td>{{ $category->name ?? '-' }}</td></tr>
            <tr><th>Mau sac</th><td>{{ $product->colors->pluck('name')->join(', ') ?: '-' }}</td></tr>
            <tr><th>Kich thuoc</th><td>{{ $product->sizes->pluck('name')->join(', ') ?: '-' }}</td></tr>
            <tr><th>Tag</th><td>{{ $product->tags->pluck('name')->join(', ') ?: '-' }}</td></tr>
            <tr><th>SKU</th><td>{{ $product->sku ?: '-' }}</td></tr>
            <tr><th>Barcode</th><td>{{ $product->barcode ?: '-' }}</td></tr>
            <tr><th>Gia</th><td>{{ number_format((float) $product->price, 0, ',', '.') }}d</td></tr>
            <tr><th>Ton kho</th><td>{{ $product->stock_qty }}</td></tr>
            <tr><th>Khoi luong (gram)</th><td>{{ $product->weight_gram }}</td></tr>
            <tr><th>Thuong hieu</th><td>{{ $product->brand ?: '-' }}</td></tr>
            <tr><th>Trang thai</th><td>{{ $product->status }}</td></tr>
            <tr><th>Mo ta</th><td>{{ $product->description ?: '-' }}</td></tr>
          </tbody>
        </table>
      </div>
    </div>

    <div class="card mt-3">
      <div class="card-header">
        <h3 class="card-title mb-0">Hinh anh ({{ $images->count() }})</h3>
      </div>
      <div class="card-body">
        <div class="product-gallery-shell">
          <div class="product-gallery-stage" data-gallery-stage>
            @if ($images->isNotEmpty())
              <button type="button" class="product-gallery-nav prev" data-gallery-prev aria-label="Anh truoc">
                <i class="bi bi-chevron-left"></i>
              </button>
              <img
                src="{{ $images->first()->image_url }}"
                alt="{{ $product->name }}"
                data-gallery-main
                data-gallery-open
              >
              <button type="button" class="product-gallery-nav next" data-gallery-next aria-label="Anh sau">
                <i class="bi bi-chevron-right"></i>
              </button>
            @else
              <div class="product-gallery-empty">Chua co hinh anh.</div>
            @endif
          </div>

          @if ($images->isNotEmpty())
            <div class="product-gallery-meta">
              <span data-gallery-counter>1 / {{ $images->count() }}</span>
              <span>Cham vao anh de phong lon, vuot hoac bam mui ten de xem anh khac</span>
            </div>

            <div class="product-gallery-strip">
              @foreach ($images as $img)
                <img
                  src="{{ $img->image_url }}"
                  alt="{{ $product->name }}"
                  class="product-gallery-thumb {{ $loop->first ? 'is-active' : '' }}"
                  data-gallery-thumb
                  data-index="{{ $loop->index }}"
                  data-src="{{ $img->image_url }}"
                >
              @endforeach
            </div>
          @endif
        </div>
      </div>
    </div>
  </div>
</div>

<div class="product-gallery-modal" data-gallery-modal hidden>
  <div class="product-gallery-modal-backdrop" data-gallery-close></div>
  <div class="product-gallery-modal-dialog" role="dialog" aria-modal="true" aria-labelledby="product-gallery-modal-title">
    <div class="product-gallery-modal-head">
      <h3 id="product-gallery-modal-title">{{ $product->name }}</h3>
      <button type="button" class="product-gallery-modal-close" data-gallery-close aria-label="Dong">
        <i class="bi bi-x-lg"></i>
      </button>
    </div>
    <div class="product-gallery-modal-body" data-gallery-swipe-area>
      <button type="button" class="product-gallery-modal-nav prev" data-gallery-prev aria-label="Anh truoc">
        <i class="bi bi-chevron-left"></i>
      </button>
      <img src="{{ $images->first()->image_url ?? '' }}" alt="{{ $product->name }}" data-gallery-modal-image>
      <button type="button" class="product-gallery-modal-nav next" data-gallery-next aria-label="Anh sau">
        <i class="bi bi-chevron-right"></i>
      </button>
    </div>
  </div>
</div>
@endsection

@push('scripts')
<script>
  (() => {
    const images = Array.from(document.querySelectorAll('[data-gallery-thumb]')).map((thumb) => ({
      src: thumb.getAttribute('data-src') || '',
      thumb,
    }));
    const mainImage = document.querySelector('[data-gallery-main]');
    const counter = document.querySelector('[data-gallery-counter]');
    const modal = document.querySelector('[data-gallery-modal]');
    const modalImage = document.querySelector('[data-gallery-modal-image]');

    if (!images.length || !mainImage || !modal || !modalImage) {
      return;
    }

    let currentIndex = 0;
    let touchStartX = 0;

    const render = () => {
      const current = images[currentIndex];
      if (!current) {
        return;
      }

      mainImage.src = current.src;
      modalImage.src = current.src;
      images.forEach((item, index) => {
        item.thumb.classList.toggle('is-active', index === currentIndex);
      });

      if (counter) {
        counter.textContent = `${currentIndex + 1} / ${images.length}`;
      }
    };

    const step = (direction) => {
      currentIndex = (currentIndex + direction + images.length) % images.length;
      render();
    };

    document.addEventListener('click', (event) => {
      const thumb = event.target.closest('[data-gallery-thumb]');
      if (thumb) {
        currentIndex = Number(thumb.getAttribute('data-index') || 0);
        render();
        return;
      }

      if (event.target.closest('[data-gallery-open]')) {
        modal.hidden = false;
        document.body.classList.add('modal-open');
        return;
      }

      if (event.target.closest('[data-gallery-close]')) {
        modal.hidden = true;
        document.body.classList.remove('modal-open');
        return;
      }

      if (event.target.closest('[data-gallery-prev]')) {
        step(-1);
        return;
      }

      if (event.target.closest('[data-gallery-next]')) {
        step(1);
      }
    });

    document.addEventListener('keydown', (event) => {
      if (event.key === 'Escape' && !modal.hidden) {
        modal.hidden = true;
        document.body.classList.remove('modal-open');
        return;
      }

      if (event.key === 'ArrowLeft') {
        step(-1);
        return;
      }

      if (event.key === 'ArrowRight') {
        step(1);
      }
    });

    const swipeArea = document.querySelector('[data-gallery-swipe-area]');
    if (swipeArea) {
      swipeArea.addEventListener('touchstart', (event) => {
        touchStartX = event.changedTouches[0]?.clientX || 0;
      }, { passive: true });

      swipeArea.addEventListener('touchend', (event) => {
        const touchEndX = event.changedTouches[0]?.clientX || 0;
        const deltaX = touchEndX - touchStartX;

        if (Math.abs(deltaX) < 40) {
          return;
        }

        if (deltaX < 0) {
          step(1);
        } else {
          step(-1);
        }
      }, { passive: true });
    }

    render();
  })();
</script>
@endpush
