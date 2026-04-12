@extends('backend.layouts.app')

@section('use_datatable', true)

@section('content')
<div class="app-content-header">
  <div class="container-fluid"><h1 class="mb-0">Sản phẩm</h1></div>
</div>
<div class="app-content">
  <div class="container-fluid">
    @if (session('success'))
      <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    <div class="card">
      <div class="card-header d-flex justify-content-between align-items-center">
        <h3 class="card-title mb-0">Danh sách sản phẩm</h3>
        <a href="{{ route('backend.products.create') }}" class="btn btn-primary btn-sm ms-auto">Thêm mới</a>
      </div>
      <div class="card-body">
        <div class="table-responsive">
          <table class="table table-striped table-bordered align-middle mb-0 js-datatable">
            <thead>
              <tr>
                <th>ID</th>
                <th>Ảnh</th>
                <th>Tên</th>
                <th>Danh mục</th>
                <th>Thông dụng</th>
                <th>Hiển thị</th>
                <th>Giá</th>
                <th>Thao tác</th>
              </tr>
            </thead>
            <tbody>
              @forelse ($products as $product)
                <tr data-product-row="{{ $product->id }}">
                  <td>{{ $product->id }}</td>
                  <td class="text-center" style="width: 84px;">
                    @if (!empty($product->primary_image_url))
                      <img
                        src="{{ $product->primary_image_url }}"
                        alt="{{ $product->name }}"
                        style="width: 56px; height: 56px; object-fit: cover; border-radius: 8px;"
                      >
                    @else
                      <div class="bg-light border rounded d-inline-flex align-items-center justify-content-center text-muted" style="width: 56px; height: 56px;">
                        -
                      </div>
                    @endif
                  </td>
                  <td title="{{ $product->name }}">{{ \Illuminate\Support\Str::limit($product->name, 45, '...') }}</td>
                  <td>{{ $categories[$product->category_id] ?? '-' }}</td>
                  <td style="min-width: 120px;">
                    <form action="{{ route('backend.products.quick-update', $product) }}" method="POST" class="mb-0 js-product-quick-update-form" data-quick-update="featured">
                      @csrf
                      @method('PATCH')
                      <input type="hidden" name="status" value="{{ $product->status }}">
                      <div class="form-check form-switch m-0 d-flex align-items-center gap-2">
                        <input
                          class="form-check-input"
                          type="checkbox"
                          name="is_featured"
                          value="1"
                          id="featured_{{ $product->id }}"
                          {{ $product->is_featured ? 'checked' : '' }}
                        >
                        <label class="form-check-label small mb-0 js-featured-label" for="featured_{{ $product->id }}">{{ $product->is_featured ? 'Bật' : 'Tắt' }}</label>
                      </div>
                    </form>
                  </td>
                  <td style="min-width: 140px;">
                    <form action="{{ route('backend.products.quick-update', $product) }}" method="POST" class="mb-0 js-product-quick-update-form" data-quick-update="status">
                      @csrf
                      @method('PATCH')
                      <input type="hidden" name="is_featured" value="{{ $product->is_featured ? 1 : 0 }}">
                      <select name="status" class="form-select form-select-sm js-product-status-select">
                        @foreach (['active' => 'Hiện', 'hidden' => 'Ẩn', 'draft' => 'Nháp'] as $value => $label)
                          <option value="{{ $value }}" {{ $product->status === $value ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                      </select>
                    </form>
                  </td>
                  <td>{{ number_format((float) $product->price, 0, ',', '.') }}đ</td>
                  <td>
                    <a href="{{ route('backend.products.show', $product) }}" class="btn btn-info btn-sm text-white">Xem</a>
                    <a href="{{ route('backend.products.edit', $product) }}" class="btn btn-outline-primary btn-sm">Sửa</a>
                    <form action="{{ route('backend.products.destroy', $product) }}" method="POST" class="d-inline js-product-delete-form" data-product-id="{{ $product->id }}" data-product-name="{{ $product->name }}">
                      @csrf
                      @method('DELETE')
                      <button class="btn btn-outline-danger btn-sm js-product-delete-button" type="submit">Xóa</button>
                    </form>
                  </td>
                </tr>
              @empty
                <tr><td colspan="8" class="text-center">Chưa có sản phẩm.</td></tr>
              @endforelse
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</div>
@endsection

@push('scripts')
<script>
  document.addEventListener('DOMContentLoaded', function () {
    if (typeof window.jQuery === 'undefined') {
      return;
    }

    const ensureAlertHost = function () {
      let host = document.getElementById('ajax-delete-alert');
      if (host) {
        return host;
      }

      const container = document.querySelector('.app-content .container-fluid');
      if (!container) {
        return null;
      }

      host = document.createElement('div');
      host.id = 'ajax-delete-alert';
      container.prepend(host);

      return host;
    };

    const showAlert = function (type, message) {
      const host = ensureAlertHost();
      if (!host) {
        return;
      }

      host.innerHTML = `<div class="alert alert-${type} alert-dismissible fade show" role="alert">
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
      </div>`;
    };

    window.jQuery(document)
      .off('change.quickUpdate')
      .on('change.quickUpdate', '.js-product-quick-update-form input[name="is_featured"], .js-product-status-select', function () {
        const field = this;
        const form = field.closest('.js-product-quick-update-form');
        if (!form) {
          return;
        }

        const $form = window.jQuery(form);
        const $checkbox = $form.find('input[name="is_featured"][type="checkbox"]');
        const $statusSelect = $form.find('select[name="status"]');
        const $label = $form.find('.js-featured-label');
        const originalChecked = $checkbox.length ? $checkbox.prop('checked') : null;
        const originalStatus = $statusSelect.length ? $statusSelect.val() : null;
        const requestData = $form.serialize();

        $form.find('input, select').prop('disabled', true);

        window.jQuery.ajax({
          url: $form.attr('action'),
          type: 'POST',
          data: requestData,
          headers: {
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
          }
        }).done(function (result) {
          if (!result || !result.success) {
            throw new Error((result && result.message) || 'Cập nhật thất bại.');
          }

          if ($label.length) {
            $label.text(result.is_featured ? 'Bật' : 'Tắt');
          }

          const row = form.closest('tr');
          if (row) {
            const featuredStatusForm = row.querySelector('form[data-quick-update="status"]');
            const featuredToggleForm = row.querySelector('form[data-quick-update="featured"]');

            if (featuredStatusForm) {
              const hiddenFeaturedInput = featuredStatusForm.querySelector('input[name="is_featured"][type="hidden"]');
              if (hiddenFeaturedInput) {
                hiddenFeaturedInput.value = result.is_featured ? '1' : '0';
              }
            }

            if (featuredToggleForm) {
              const toggleCheckbox = featuredToggleForm.querySelector('input[name="is_featured"][type="checkbox"]');
              const toggleLabel = featuredToggleForm.querySelector('.js-featured-label');
              const hiddenStatusInput = featuredToggleForm.querySelector('input[name="status"]');

              if (toggleCheckbox) {
                toggleCheckbox.checked = !!result.is_featured;
              }

              if (toggleLabel) {
                toggleLabel.textContent = result.is_featured ? 'Bật' : 'Tắt';
              }

              if (hiddenStatusInput) {
                hiddenStatusInput.value = result.status;
              }
            }

            if (featuredStatusForm) {
              const statusSelect = featuredStatusForm.querySelector('select[name="status"]');
              if (statusSelect) {
                statusSelect.value = result.status;
              }
            }
          }

          showAlert('success', result.message || 'Đã cập nhật thành công.');
        }).fail(function (xhr) {
          const message = xhr.responseJSON && xhr.responseJSON.message
            ? xhr.responseJSON.message
            : 'Có lỗi xảy ra khi cập nhật.';

          if ($checkbox.length && originalChecked !== null) {
            $checkbox.prop('checked', originalChecked);
          }

          if ($statusSelect.length && originalStatus !== null) {
            $statusSelect.val(originalStatus);
          }

          showAlert('danger', message);
        }).always(function () {
          $form.find('input, select').prop('disabled', false);
        });
      });
  });
</script>
@endpush
