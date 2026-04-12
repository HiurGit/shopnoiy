@extends('frontend.layouts.app')

@section('title', 'Giỏ hàng')

@section('content')
<main class="phone cart-phone">
  <header class="cart-topbar">
    <a href="{{ route('frontend.home') }}" class="logo">{{ $frontendLogoPrimary }}@if ($frontendLogoAccent) <span>{{ $frontendLogoAccent }}</span>@endif</a>
    <div class="actions">
      <i class="bi bi-search"></i>
      <a href="{{ route('frontend.cart') }}" class="bell-wrap" aria-label="Mở giỏ hàng"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true" data-slot="icon" width="20" height="20"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 10.5V6a3.75 3.75 0 1 0-7.5 0v4.5m11.356-1.993 1.263 12c.07.665-.45 1.243-1.119 1.243H4.25a1.125 1.125 0 0 1-1.12-1.243l1.264-12A1.125 1.125 0 0 1 5.513 7.5h12.974c.576 0 1.059.435 1.119 1.007ZM8.625 10.5a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm7.5 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z"></path></svg></a>
      <i class="bi bi-person-circle"></i>
    </div>
  </header>

  <section class="cart-subhead">
    <a href="{{ url()->previous() }}" class="cart-subhead-back" aria-label="Quay lại" data-history-back="true">
      <i class="bi bi-arrow-left"></i>
    </a>
    <h1>Giỏ hàng</h1>
    <span class="cart-subhead-spacer"></span>
  </section>

  <section class="cart-page-panel">
    <section class="cart-page">
       
      <div data-cart-page></div>
    </section>
  </section>

  <section class="cart-summary-bar">
    <div class="cart-summary-total">
      <span>Tổng cộng</span>
      <strong data-cart-subtotal>0đ</strong>
    </div>
    <a href="{{ route('frontend.checkout') }}" class="cart-order-btn" data-cart-checkout>Đặt hàng</a>
  </section>

  <div class="cart-edit-modal" data-cart-edit-modal hidden>
    <div class="cart-edit-backdrop" data-cart-edit-close></div>
    <section class="cart-edit-sheet">
      <div class="cart-edit-head">
        <h2>Cập nhật sản phẩm</h2>
        <button type="button" class="cart-edit-close" aria-label="Đóng" data-cart-edit-close>
          <i class="bi bi-x-lg"></i>
        </button>
      </div>

      <div class="cart-edit-product">
        <div class="cart-edit-thumb">
          <img src="" alt="" data-cart-edit-image />
        </div>
        <div class="cart-edit-info">
          <h3 data-cart-edit-name></h3>
          <p data-cart-edit-current></p>
        </div>
      </div>

      <div class="cart-edit-block">
        <p class="cart-edit-label">Màu sắc: <span data-cart-edit-color-label></span></p>
        <div class="cart-edit-colors" data-cart-edit-colors></div>
      </div>

      <div class="cart-edit-block">
        <p class="cart-edit-label">Kích thước: <span data-cart-edit-size-label></span></p>
        <div class="cart-edit-sizes" data-cart-edit-sizes></div>
      </div>

      <button type="button" class="cart-edit-submit" data-cart-edit-submit>Cập nhật</button>
    </section>
  </div>
</main>
@endsection

@push('scripts')
<script>
  (() => {
    const cartPage = document.querySelector('[data-cart-page]');
    const cartListHead = document.querySelector('[data-cart-list-head]');
    const cartCount = document.querySelector('[data-cart-count]');
    const subtotalElement = document.querySelector('[data-cart-subtotal]');
    const checkoutButton = document.querySelector('[data-cart-checkout]');
    const editModal = document.querySelector('[data-cart-edit-modal]');
    const editImage = document.querySelector('[data-cart-edit-image]');
    const editName = document.querySelector('[data-cart-edit-name]');
    const editCurrent = document.querySelector('[data-cart-edit-current]');
    const editColorLabel = document.querySelector('[data-cart-edit-color-label]');
    const editSizeLabel = document.querySelector('[data-cart-edit-size-label]');
    const editColors = document.querySelector('[data-cart-edit-colors]');
    const editSizes = document.querySelector('[data-cart-edit-sizes]');
    const editSubmit = document.querySelector('[data-cart-edit-submit]');
    let editState = null;

    if (!cartPage || !window.ShopNoiyCart) {
      return;
    }

    const formatMoney = (value) => `${new Intl.NumberFormat('vi-VN').format(Number(value) || 0)}đ`;
    const escapeHtml = (value) => String(value)
      .replace(/&/g, '&amp;')
      .replace(/</g, '&lt;')
      .replace(/>/g, '&gt;')
      .replace(/"/g, '&quot;')
      .replace(/'/g, '&#39;');

    const renderCart = () => {
      const items = window.ShopNoiyCart.getItems();
      const subtotal = items.reduce((sum, item) => sum + ((Number(item.price) || 0) * (Number(item.qty) || 0)), 0);

      if (!items.length) {
        cartPage.innerHTML = `
          <div class="cart-empty">
            <strong>Giỏ hàng của bạn đang trống</strong>
            <p>Giỏ hàng của bạn chưa có sản phẩm nào. Khám phá ngay các mẫu sản phẩm đang được yêu thích tại shop nhé!</p>
          </div>
        `;

        if (cartListHead) {
          cartListHead.hidden = true;
        }

        if (subtotalElement) {
          subtotalElement.textContent = formatMoney(0);
        }

        if (checkoutButton) {
          checkoutButton.classList.add('is-disabled');
          checkoutButton.setAttribute('aria-disabled', 'true');
        }

        return;
      }

      if (cartListHead) {
        cartListHead.hidden = false;
      }

      if (cartCount) {
        const totalItems = items.reduce((sum, item) => sum + (Number(item.qty) || 0), 0);
        cartCount.textContent = `${totalItems} sản phẩm`;
      }

      cartPage.innerHTML = items.map((item, index) => `
        <article class="cart-item" data-cart-index="${index}">
          <div class="cart-thumb">
            <img src="${escapeHtml(item.image_url || '')}" alt="${escapeHtml(item.name || '')}" loading="lazy" decoding="async" />
          </div>

          <div class="cart-info">
            <div class="cart-head-row">
              <h2>${escapeHtml(item.name || '')}</h2>
              <button class="cart-remove" aria-label="Xóa" type="button" data-cart-remove="${index}"><i class="bi bi-trash"></i></button>
            </div>

            <button class="cart-variant" type="button" data-cart-edit-trigger="${index}">${escapeHtml([item.color, item.size].filter(Boolean).join(' | ') || '-')}</button>

            <div class="cart-bottom-row">
              <div class="cart-qty">
                <button type="button" aria-label="Giảm" data-cart-qty="decrease" data-cart-index="${index}" ${Number(item.qty) <= 1 ? 'disabled' : ''}>-</button>
                <span>${Number(item.qty) || 1}</span>
                <button type="button" aria-label="Tăng" data-cart-qty="increase" data-cart-index="${index}" ${Number(item.qty) >= 99 ? 'disabled' : ''}>+</button>
              </div>
              <div class="cart-price">${formatMoney(item.price)}</div>
            </div>
          </div>
        </article>
      `).join('');

      if (subtotalElement) {
        subtotalElement.textContent = formatMoney(subtotal);
      }

      if (checkoutButton) {
        checkoutButton.classList.remove('is-disabled');
        checkoutButton.setAttribute('aria-disabled', 'false');
      }
    };

    cartPage.addEventListener('click', (event) => {
      const removeButton = event.target.closest('[data-cart-remove]');
      if (removeButton) {
        window.ShopNoiyCart.removeItem(Number(removeButton.dataset.cartRemove));
        return;
      }

      const editTrigger = event.target.closest('[data-cart-edit-trigger]');
      if (editTrigger) {
        window.ShopNoiyVisitorTracking?.update({ activity_label: 'Đang chỉnh sản phẩm trong giỏ' });
        openEditModal(Number(editTrigger.dataset.cartEditTrigger));
        return;
      }

      const qtyButton = event.target.closest('[data-cart-qty]');
      if (qtyButton) {
        const index = Number(qtyButton.dataset.cartIndex);
        const item = window.ShopNoiyCart.getItems()[index];
        if (!item) {
          return;
        }

        const nextQty = (Number(item.qty) || 1) + (qtyButton.dataset.cartQty === 'increase' ? 1 : -1);
        window.ShopNoiyCart.updateQuantity(index, nextQty);
      }
    });

    const closeEditModal = () => {
      if (!editModal) {
        return;
      }

      editModal.classList.remove('is-visible');
      window.setTimeout(() => {
        editModal.hidden = true;
      }, 180);
      editState = null;
    };

    const renderEditOptions = () => {
      if (!editState) {
        return;
      }

      if (editColorLabel) {
        editColorLabel.textContent = editState.selectedColor || '-';
      }

      if (editSizeLabel) {
        editSizeLabel.textContent = editState.selectedSize || '-';
      }

      if (editColors) {
        editColors.innerHTML = (editState.colors || []).map((color) => `
          <button
            type="button"
            class="cart-edit-color ${color.name === editState.selectedColor ? 'active' : ''}"
            style="--c:${escapeHtml(color.hex_code || '#cccccc')}"
            data-edit-color="${escapeHtml(color.name)}"
            aria-label="${escapeHtml(color.name)}"
          ></button>
        `).join('');
      }

      if (editSizes) {
        editSizes.innerHTML = (editState.sizes || []).map((size) => `
          <button
            type="button"
            class="cart-edit-size ${size.name === editState.selectedSize ? 'active' : ''}"
            data-edit-size="${escapeHtml(size.name)}"
          >${escapeHtml(size.name)}</button>
        `).join('');
      }
    };

    const openEditModal = async (index) => {
      const item = window.ShopNoiyCart.getItems()[index];
      if (!item || !editModal) {
        return;
      }

      editModal.hidden = false;
      editModal.classList.add('is-visible');

      if (editImage) {
        editImage.src = item.image_url || '';
        editImage.alt = item.name || '';
      }

      if (editName) {
        editName.textContent = item.name || '';
      }

      if (editCurrent) {
        editCurrent.textContent = [item.color, item.size].filter(Boolean).join(', ') || '-';
      }

      editState = {
        index,
        item,
        colors: [],
        sizes: [],
        selectedColor: item.color || '',
        selectedSize: item.size || '',
      };

      renderEditOptions();

      try {
        const response = await fetch(`${@json(route('frontend.product-config', ['product' => '__PRODUCT__']))}`.replace('__PRODUCT__', item.product_id), {
          headers: {
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
          }
        });

        if (!response.ok) {
          return;
        }

        const payload = await response.json();
        if (!editState || editState.index !== index) {
          return;
        }

        editState.colors = Array.isArray(payload.colors) ? payload.colors : [];
        editState.sizes = Array.isArray(payload.sizes) ? payload.sizes : [];

        if (!editState.selectedColor && editState.colors[0]) {
          editState.selectedColor = editState.colors[0].name;
        }

        if (!editState.selectedSize && editState.sizes[0]) {
          editState.selectedSize = editState.sizes[0].name;
        }

        renderEditOptions();
      } catch (error) {
        // Keep current selection if loading config fails.
      }
    };

    if (editModal) {
      editModal.addEventListener('click', (event) => {
        const closeTarget = event.target.closest('[data-cart-edit-close]');
        if (closeTarget) {
          closeEditModal();
          return;
        }

        const colorButton = event.target.closest('[data-edit-color]');
        if (colorButton && editState) {
          editState.selectedColor = colorButton.dataset.editColor || '';
          renderEditOptions();
          return;
        }

        const sizeButton = event.target.closest('[data-edit-size]');
        if (sizeButton && editState) {
          editState.selectedSize = sizeButton.dataset.editSize || '';
          renderEditOptions();
        }
      });
    }

    if (editSubmit) {
      editSubmit.addEventListener('click', () => {
        if (!editState) {
          return;
        }

        window.ShopNoiyCart.updateItem(editState.index, {
          color: editState.selectedColor || '',
          size: editState.selectedSize || '',
        });

        closeEditModal();
      });
    }

    if (checkoutButton) {
      checkoutButton.addEventListener('click', (event) => {
        if (checkoutButton.getAttribute('aria-disabled') === 'true') {
          event.preventDefault();
          return;
        }

        window.ShopNoiyVisitorTracking?.update({ activity_label: 'Từ giỏ hàng chuyển sang thanh toán' });
      });
    }

    window.addEventListener('shopnoiy-cart-updated', renderCart);
    renderCart();
  })();
</script>
@endpush
