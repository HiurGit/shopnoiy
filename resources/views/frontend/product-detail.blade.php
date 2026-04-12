@extends('frontend.layouts.app')

@section('title', $product->name)
@section('meta_title', $product->name)
@section('meta_description', \Illuminate\Support\Str::limit(trim(strip_tags($product->description ?: $frontendMetaDescription)), 180))
@section('og_image', $gallery->first()->resolved_url ?? ($frontendMetaOgImage ?? ''))
@section('og_type', 'product')

@push('vendor_styles')
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css" />
@endpush

@section('content')
<main class="phone pd-phone">
  <header class="topbar pd-topbar">
    <div class="topbar-row">
      <a href="{{ route('frontend.home') }}" class="logo">{{ $frontendLogoPrimary }}@if ($frontendLogoAccent)<span>{{ $frontendLogoAccent }}</span>@endif</a>
      <a href="{{ route('frontend.search') }}" class="search-form topbar-search search-entry-link" aria-label="Mở tìm kiếm">
        <i class="bi bi-search search-icon"></i>
        <span class="search-entry-text">Tìm sản phẩm...</span>
        <span class="search-entry-btn">Tìm</span>
      </a>
      <div class="actions">
        <a href="{{ route('frontend.cart') }}" class="bell-wrap" aria-label="Mở giỏ hàng"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true" data-slot="icon" width="20" height="20"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 10.5V6a3.75 3.75 0 1 0-7.5 0v4.5m11.356-1.993 1.263 12c.07.665-.45 1.243-1.119 1.243H4.25a1.125 1.125 0 0 1-1.12-1.243l1.264-12A1.125 1.125 0 0 1 5.513 7.5h12.974c.576 0 1.059.435 1.119 1.007ZM8.625 10.5a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm7.5 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z"></path></svg></a>
        <i class="bi bi-person-circle"></i>
      </div>
    </div>
  </header>

  <section class="cart-subhead">
    <a href="{{ url()->previous() }}" class="cart-subhead-back" aria-label="Quay lại" data-history-back="true">
      <i class="bi bi-arrow-left"></i>
    </a>
    <h1>Chi tiết sản phẩm</h1>
    <span class="cart-subhead-spacer"></span>
  </section>

  <section class="pd-hero-panel">
    <section class="pd-gallery">
      <div class="pd-gallery-swiper swiper" aria-label="Hình ảnh sản phẩm">
        <button
          type="button"
          class="pd-share-btn"
          aria-label="Chia sẻ sản phẩm"
          data-share-toggle
          aria-expanded="false"
          data-share-default-label="Chia sẻ"
          data-share-success-label="Đã sao chép link"
          data-share-error-label="Không thể chia sẻ"
        >
          <i class="bi bi-share"></i>
          <span>Chia sẻ</span>
        </button>
        <button type="button" class="pd-gallery-zoom-hint" aria-label="Xem ảnh lớn" data-gallery-open>
          <i class="bi bi-arrows-fullscreen"></i>
          <span>Chạm để phóng to</span>
        </button>
        <div class="swiper-wrapper">
          @foreach ($gallery as $index => $image)
            <div class="swiper-slide">
              <img
                @if ($index === 0)
                  src="{{ $image->resolved_url }}"
                @endif
                data-src="{{ $image->resolved_url }}"
                alt="{{ $product->name }} - hình {{ $index + 1 }}"
                decoding="async"
                loading="{{ $index === 0 ? 'eager' : 'lazy' }}"
                fetchpriority="{{ $index === 0 ? 'high' : 'low' }}"
                class="{{ $index === 0 ? 'is-loaded' : '' }}"
                data-gallery-open
                data-gallery-index="{{ $index }}"
              />
            </div>
          @endforeach
        </div>
        @if ($gallery->count() > 1)
          <div class="pd-gallery-scrollbar swiper-scrollbar" aria-label="Thanh cuộn hình sản phẩm"></div>
          <span class="pd-gallery-index">1/{{ $gallery->count() }}</span>
        @endif
      </div>

      @if ($gallery->count() > 1)
        <div class="pd-gallery-thumbs swiper" aria-label="Danh sách ảnh sản phẩm">
          <div class="swiper-wrapper">
            @foreach ($gallery as $index => $image)
              <div class="swiper-slide">
                <img
                  @if ($index < 4)
                    src="{{ $image->resolved_url }}"
                  @endif
                  data-src="{{ $image->resolved_url }}"
                  alt="{{ $product->name }} thumbnail {{ $index + 1 }}"
                  loading="lazy"
                  decoding="async"
                  fetchpriority="low"
                  class="{{ $index < 4 ? 'is-loaded' : '' }}"
                />
              </div>
            @endforeach
          </div>
        </div>
      @endif
    </section>

    <div class="pd-gallery-lightbox" data-gallery-lightbox hidden>
      <div class="pd-gallery-lightbox-backdrop" data-gallery-close></div>
      <div class="pd-gallery-lightbox-dialog" role="dialog" aria-modal="true" aria-label="Xem ảnh sản phẩm">
        <div class="pd-gallery-lightbox-head">
          <span class="pd-gallery-lightbox-count" data-gallery-lightbox-count>1/{{ $gallery->count() }}</span>
          <button type="button" class="pd-gallery-lightbox-close" aria-label="Đóng ảnh lớn" data-gallery-close>
            <i class="bi bi-x-lg"></i>
          </button>
        </div>
        <div class="pd-gallery-lightbox-body">
          @if ($gallery->count() > 1)
            <button type="button" class="pd-gallery-lightbox-nav prev" aria-label="Ảnh trước" data-gallery-prev>
              <i class="bi bi-chevron-left"></i>
            </button>
          @endif
          <img
            src="{{ $gallery->first()->resolved_url ?? '' }}"
            alt="{{ $product->name }}"
            data-gallery-lightbox-image
            decoding="async"
            loading="lazy"
            fetchpriority="low"
          />
          @if ($gallery->count() > 1)
            <button type="button" class="pd-gallery-lightbox-nav next" aria-label="Ảnh sau" data-gallery-next>
              <i class="bi bi-chevron-right"></i>
            </button>
          @endif
        </div>
      </div>
    </div>

    <section class="pd-info">
      <p class="pd-price">{{ number_format((float) $product->price, 0, ',', '.') }}đ</p>
      <h1>{{ $product->name }}</h1>
      <p class="pd-sku">{{ $product->sku ?: ('SKU-' . $product->id) }} <i class="bi bi-copy"></i></p>
      <input type="hidden" id="selected-color" value="" />
      <input type="hidden" id="selected-size" value="" />
      <input type="hidden" id="selected-quantity" value="1" />

      <div class="pd-block">
        <p class="pd-label">Màu sắc:</p>
        <p class="pd-choice-value" data-choice-display="color">Chưa chọn màu</p>
        <div class="pd-colors">
          @forelse ($product->colors as $color)
            <button
              type="button"
              class="pd-color"
              style="--c: {{ $color->hex_code ?: '#cccccc' }}"
              aria-label="{{ $color->name }}"
              aria-pressed="false"
              data-option-group="color"
              data-option-value="{{ $color->name }}"
            ></button>
          @empty
            <span>Đang cập nhật</span>
          @endforelse
        </div>
      </div>

      <div class="pd-block">
        <div class="pd-size-head">
          <p class="pd-label">Kích cỡ:</p>
        </div>
        <p class="pd-choice-value" data-choice-display="size">Chưa chọn kích cỡ</p>
        <div class="pd-sizes">
          @forelse ($product->sizes as $size)
            <button
              type="button"
              aria-pressed="false"
              data-option-group="size"
              data-option-value="{{ $size->name }}"
            >{{ $size->name }}</button>
          @empty
            <span>Đang cập nhật</span>
          @endforelse
        </div>
      </div>

      <div class="pd-cta-row">
        <div class="pd-qty" data-qty-control>
          <button type="button" aria-label="Giảm" data-qty-action="decrease">-</button>
          <span data-qty-value>1</span>
          <button type="button" aria-label="Tăng" data-qty-action="increase">+</button>
        </div>
        <a href="{{ route('frontend.cart') }}" class="pd-add-cart" aria-label="Thêm vào giỏ hàng" data-requires-selection="true" data-cart-action="add" data-default-label="Thêm vào giỏ">
          <span>Thêm vào giỏ</span>
          <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true" data-slot="icon" width="20" height="20"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 10.5V6a3.75 3.75 0 1 0-7.5 0v4.5m11.356-1.993 1.263 12c.07.665-.45 1.243-1.119 1.243H4.25a1.125 1.125 0 0 1-1.12-1.243l1.264-12A1.125 1.125 0 0 1 5.513 7.5h12.974c.576 0 1.059.435 1.119 1.007ZM8.625 10.5a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm7.5 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z"></path></svg>
        </a>
      </div>
    </section>
  </section>

  <section class="pd-detail-section">
    <details class="pd-detail-item">
      <summary>
        THÔNG TIN SẢN PHẨM
        <i class="bi bi-plus"></i>
      </summary>
      <div class="pd-detail-content">
        <p>{{ $product->description ?: 'Thong tin chi tiet dang duoc cap nhat.' }}</p>
        @if ($product->tags->isNotEmpty())
          <div class="pd-product-tags">
            <div class="pd-product-tags-label">Tag sản phẩm</div>
            <div class="pd-product-tags-list">
              @foreach ($product->tags as $tag)
                <span class="pd-product-tag">{{ $tag->name }}</span>
              @endforeach
            </div>
          </div>
        @endif
      </div>
    </details>
   <details class="pd-detail-item">
      <summary>
        HƯỚNG DẪN CHỌN SIZE
        <i class="bi bi-plus"></i>
      </summary>
      <div class="pd-detail-content">
        @if (!empty($productSizeGuideRows))
          <div class="pd-size-guide-table-wrap">
            <table class="pd-size-guide-table">
              <thead>
                <tr>
                  <th>Cân nặng</th>
                  <th>Size (VN/Quốc tế)</th>
                  <th>Gợi ý</th>
                </tr>
              </thead>
              <tbody>
                @foreach ($productSizeGuideRows as $row)
                  <tr>
                    <td>{{ $row['weight'] }}</td>
                    <td>{{ $row['size'] }}</td>
                    <td>{{ $row['suggestion'] }}</td>
                  </tr>
                @endforeach
              </tbody>
            </table>
          </div>
        @else
          <p class="pd-size-guide-note">{!! nl2br(e($productSizeGuide)) !!}</p>
        @endif
      </div>
    </details>
    <details class="pd-detail-item">
      <summary>
        HƯỚNG DẪN BẢO QUẢN
        <i class="bi bi-plus"></i>
      </summary>
      <div class="pd-detail-content">
        <p>{!! nl2br(e($productCarePolicy)) !!}</p>
      </div>
    </details>

    <details class="pd-detail-item">
      <summary>
       CHÍNH SÁCH ĐỔI TRẢ
        <i class="bi bi-plus"></i>
      </summary>
      <div class="pd-detail-content">
        <p>{!! nl2br(e($productReturnPolicy)) !!}</p>
      </div>
    </details>
  </section>

  @if ($relatedProducts->isNotEmpty())
    <section class="featured-group">
      <div class="featured-panel">
        <div class="featured-panel-head">
          <h4>Sản phẩm liên quan</h4>
        </div>
        <div class="featured-products-swiper swiper" wire:ignore>
          <div class="swiper-wrapper">
            @foreach ($relatedProducts as $related)
              <article class="featured-product-card swiper-slide">
                <a href="{{ route('frontend.product-detail', ['slug' => $related->slug]) }}" class="featured-product-link">
                  <div class="featured-thumb">
                    <img src="{{ $related->primary_image_url }}" alt="{{ $related->name }}" loading="lazy" decoding="async" fetchpriority="low" />
                  </div>
                  <h3>{{ $related->name }}</h3>
                  <div class="featured-price-row">
                    <strong>{{ number_format((float) $related->price, 0, ',', '.') }}đ</strong>
                  </div>
                </a>
              </article>
            @endforeach
          </div>
        </div>
      </div>
    </section>
  @endif

  <button class="scroll-top-btn" type="button" aria-label="Cuộn lên đầu trang">
    <i class="bi bi-arrow-up"></i>
  </button>
</main>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>
<script>
  (() => {
    const productPayload = {
      product_id: @json($product->id),
      slug: @json($product->slug),
      name: @json($product->name),
      price: @json((float) $product->price),
      image_url: @json($gallery->first()->resolved_url ?? ''),
    };

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

    const galleryElement = document.querySelector('.pd-gallery-swiper');
    const thumbsElement = document.querySelector('.pd-gallery-thumbs');
    const galleryIndex = document.querySelector('.pd-gallery-index');
    const shareButton = document.querySelector('[data-share-toggle]');
    let shareFeedbackTimer = null;
    let thumbsSwiper = null;
    let gallerySwiper = null;

    const loadImage = (image) => {
      if (!image || image.dataset.loaded === 'true') {
        return;
      }

      const nextSrc = image.dataset.src;
      if (!nextSrc) {
        return;
      }

      image.src = nextSrc;
      image.dataset.loaded = 'true';
      image.classList.add('is-loaded');
    };

    const loadGalleryWindow = (activeIndex = 0) => {
      if (galleryElement) {
        Array.from(galleryElement.querySelectorAll('.swiper-slide img')).forEach((image) => {
          const imageIndex = Number(image.dataset.galleryIndex || 0);
          if (Math.abs(imageIndex - activeIndex) <= 1) {
            loadImage(image);
          }
        });
      }

      if (thumbsElement) {
        Array.from(thumbsElement.querySelectorAll('.swiper-slide img')).forEach((image, index) => {
          if (Math.abs(index - activeIndex) <= 4) {
            loadImage(image);
          }
        });
      }
    };

    if (thumbsElement) {
      thumbsSwiper = new Swiper(thumbsElement, {
        spaceBetween: 8,
        slidesPerView: 4.2,
        watchSlidesProgress: true,
        freeMode: true,
        breakpoints: {
          360: {
            slidesPerView: 4.6
          }
        }
      });
    }

    if (galleryElement) {
      loadGalleryWindow(0);

      gallerySwiper = new Swiper(galleryElement, {
        slidesPerView: 1,
        spaceBetween: 0,
        grabCursor: true,
        watchOverflow: true,
        scrollbar: {
          el: '.pd-gallery-scrollbar',
          draggable: true,
          hide: false
        },
        thumbs: thumbsSwiper ? { swiper: thumbsSwiper } : undefined,
        on: {
          init(swiper) {
            loadGalleryWindow(swiper.realIndex);
            if (galleryIndex) {
              galleryIndex.textContent = `${swiper.realIndex + 1}/${swiper.slides.length}`;
            }
          },
          slideChange(swiper) {
            loadGalleryWindow(swiper.realIndex);
            if (galleryIndex) {
              galleryIndex.textContent = `${swiper.realIndex + 1}/${swiper.slides.length}`;
            }
          }
        }
      });
    }

    const setShareButtonState = (type) => {
      if (!shareButton) {
        return;
      }

      const label = shareButton.querySelector('span');
      if (!label) {
        return;
      }

      window.clearTimeout(shareFeedbackTimer);
      shareButton.classList.remove('is-success', 'is-error');

      if (type === 'success') {
        label.textContent = shareButton.dataset.shareSuccessLabel || 'Da sao chep link';
        shareButton.classList.add('is-success');
      } else if (type === 'error') {
        label.textContent = shareButton.dataset.shareErrorLabel || 'Khong the sao chep';
        shareButton.classList.add('is-error');
      } else {
        label.textContent = shareButton.dataset.shareDefaultLabel || 'Chia se';
      }

      if (type === 'success' || type === 'error') {
        shareFeedbackTimer = window.setTimeout(() => {
          shareButton.classList.remove('is-success', 'is-error');
          label.textContent = shareButton.dataset.shareDefaultLabel || 'Chia se';
        }, 2200);
      }
    };

    const copyShareUrl = async (urlToCopy) => {
      if (navigator.clipboard?.writeText) {
        await navigator.clipboard.writeText(urlToCopy);
        return true;
      }

      const tempInput = document.createElement('input');
      tempInput.value = urlToCopy;
      tempInput.setAttribute('readonly', 'readonly');
      tempInput.style.position = 'absolute';
      tempInput.style.left = '-9999px';
      document.body.appendChild(tempInput);
      tempInput.select();
      tempInput.setSelectionRange(0, tempInput.value.length);
      const copied = document.execCommand('copy');
      document.body.removeChild(tempInput);

      if (!copied) {
        throw new Error('Copy command failed');
      }

      return true;
    };

    if (shareButton) {
      shareButton.addEventListener('click', async () => {
        try {
          await copyShareUrl(window.location.href);
          setShareButtonState('success');
        } catch (error) {
          setShareButtonState('error');
        }
      });
    }

    const relatedProductsElement = document.querySelector('.featured-products-swiper');
    if (relatedProductsElement) {
      new Swiper(relatedProductsElement, {
        slidesPerView: 2,
        spaceBetween: 10,
        grabCursor: true,
        watchOverflow: true
      });
    }

    const selectedColorInput = document.querySelector('#selected-color');
    const selectedSizeInput = document.querySelector('#selected-size');
    const selectedQuantityInput = document.querySelector('#selected-quantity');
    const colorDisplay = document.querySelector('[data-choice-display="color"]');
    const sizeDisplay = document.querySelector('[data-choice-display="size"]');
    const ctaButtons = document.querySelectorAll('[data-requires-selection="true"]');
    const cartActionButtons = document.querySelectorAll('[data-cart-action]');
    let addCartFeedbackTimer = null;
    const qtyControl = document.querySelector('[data-qty-control]');
    const qtyValue = document.querySelector('[data-qty-value]');
    const qtyButtons = document.querySelectorAll('[data-qty-action]');
    const hasColorOptions = document.querySelectorAll('[data-option-group="color"]').length > 0;
    const hasSizeOptions = document.querySelectorAll('[data-option-group="size"]').length > 0;
    const minQuantity = 1;
    const maxQuantity = 99;

    const syncSelectionSummary = () => {
      const selectedColor = selectedColorInput?.value?.trim() ?? '';
      const selectedSize = selectedSizeInput?.value?.trim() ?? '';
      const selectedQuantity = selectedQuantityInput?.value?.trim() ?? '1';
      const isReady = (!hasColorOptions || selectedColor) && (!hasSizeOptions || selectedSize);

      ctaButtons.forEach((button) => {
        button.classList.toggle('is-disabled', !isReady);
        button.setAttribute('aria-disabled', isReady ? 'false' : 'true');
      });
    };

    const activateOption = (group, value, trigger) => {
      document.querySelectorAll(`[data-option-group="${group}"]`).forEach((button) => {
        const isActive = button === trigger;
        button.classList.toggle('active', isActive);
        button.setAttribute('aria-pressed', isActive ? 'true' : 'false');
      });

      if (group === 'color') {
        if (selectedColorInput) selectedColorInput.value = value;
        if (colorDisplay) colorDisplay.textContent = `Đã chọn: ${value}`;
      }

      if (group === 'size') {
        if (selectedSizeInput) selectedSizeInput.value = value;
        if (sizeDisplay) sizeDisplay.textContent = `Đã chọn: ${value}`;
      }

      syncSelectionSummary();
    };

    const syncQuantity = (nextQuantity) => {
      const safeQuantity = Math.max(minQuantity, Math.min(maxQuantity, Number(nextQuantity) || minQuantity));

      if (selectedQuantityInput) {
        selectedQuantityInput.value = String(safeQuantity);
      }

      if (qtyValue) {
        qtyValue.textContent = String(safeQuantity);
      }

      qtyButtons.forEach((button) => {
        if (button.dataset.qtyAction === 'decrease') {
          button.disabled = safeQuantity <= minQuantity;
        }

        if (button.dataset.qtyAction === 'increase') {
          button.disabled = safeQuantity >= maxQuantity;
        }
      });

      syncSelectionSummary();
    };

    document.querySelectorAll('[data-option-group]').forEach((button) => {
      button.addEventListener('click', () => {
        activateOption(button.dataset.optionGroup, button.dataset.optionValue, button);
      });
    });

    const firstColorButton = document.querySelector('[data-option-group="color"]');
    const firstSizeButton = document.querySelector('[data-option-group="size"]');

    if (firstColorButton) {
      activateOption('color', firstColorButton.dataset.optionValue, firstColorButton);
    }

    if (firstSizeButton) {
      activateOption('size', firstSizeButton.dataset.optionValue, firstSizeButton);
    }

    if (qtyControl) {
      qtyButtons.forEach((button) => {
        button.addEventListener('click', () => {
          const currentQuantity = Number(selectedQuantityInput?.value || minQuantity);
          const delta = button.dataset.qtyAction === 'increase' ? 1 : -1;
          syncQuantity(currentQuantity + delta);
        });
      });
    }

    syncQuantity(selectedQuantityInput?.value || minQuantity);
    syncSelectionSummary();
    window.ShopNoiyVisitorTracking?.trackInterest({
      event_type: 'product_view',
      product_id: Number(productPayload.product_id || productPayload.id || 0) || null,
      product_slug: productPayload.slug || '',
      product_name: productPayload.name || '',
      qty: 1,
    });

    ctaButtons.forEach((button) => {
      button.addEventListener('click', (event) => {
        if (button.getAttribute('aria-disabled') === 'true') {
          event.preventDefault();
        }
      });
    });

    cartActionButtons.forEach((button) => {
      button.addEventListener('click', (event) => {
        if (button.getAttribute('aria-disabled') === 'true') {
          event.preventDefault();
          return;
        }

        if (!window.ShopNoiyCart) {
          return;
        }

        event.preventDefault();

        const addedItem = {
          ...productPayload,
          color: selectedColorInput?.value || '',
          size: selectedSizeInput?.value || '',
          qty: Number(selectedQuantityInput?.value || 1),
        };

        window.ShopNoiyCart.addItem(addedItem);
        window.ShopNoiyCart.showToast(addedItem);
        window.ShopNoiyVisitorTracking?.update({
          activity_label: 'Đã thêm sản phẩm vào giỏ',
          meta: {
            product_name: productPayload.name || '',
            product_slug: productPayload.slug || '',
          }
        });
        window.ShopNoiyVisitorTracking?.trackInterest({
          event_type: 'add_to_cart',
          product_id: Number(productPayload.product_id || productPayload.id || 0) || null,
          product_slug: productPayload.slug || '',
          product_name: productPayload.name || '',
          qty: Number(selectedQuantityInput?.value || 1),
        });

        const label = button.querySelector('span');
        if (label) {
          window.clearTimeout(addCartFeedbackTimer);
          label.textContent = 'Đã thêm vào giỏ';
          button.classList.add('is-added');
          addCartFeedbackTimer = window.setTimeout(() => {
            label.textContent = button.dataset.defaultLabel || 'Thêm vào giỏ';
            button.classList.remove('is-added');
          }, 1400);
        }
      });
    });
  })();
</script>
@endpush


