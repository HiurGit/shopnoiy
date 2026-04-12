@extends('backend.layouts.app')

@section('title', 'Danh mục')

@section('content')
<div class="app-content-header">
  <div class="container-fluid d-flex justify-content-between align-items-center">
    <h1 class="mb-0">Danh mục</h1>
    <div class="d-flex gap-2">
      <a href="{{ route('backend.categories.create', ['type' => 'parent']) }}" class="btn btn-primary btn-sm">Thêm danh mục cha</a>
      <a href="{{ route('backend.categories.create', ['type' => 'child']) }}" class="btn btn-outline-primary btn-sm">Thêm danh mục con</a>
      <a href="{{ route('backend.categories.create', ['type' => 'grandchild']) }}" class="btn btn-outline-primary btn-sm">Thêm danh mục cháu</a>
    </div>
  </div>
</div>

<div class="app-content">
  <div class="container-fluid">
    @if (session('success'))
      <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    @if (session('error'))
      <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <div class="card mb-3">
      <div class="card-header">
        <h3 class="card-title mb-0">Danh mục cha (Cấp 1)</h3>
      </div>
      <div class="card-body">
        <table class="table table-striped table-hover align-middle mb-0">
          <thead>
            <tr>
              <th>ID</th>
              <th>Tên</th>
              <th>Slug</th>
              <th>Đối tượng</th>
              <th>Thứ tự</th>
              <th>Trạng thái</th>
              <th>Cập nhật lúc</th>
              <th>Thao tác</th>
            </tr>
          </thead>
          <tbody>
            @forelse ($parentCategories as $row)
              <tr>
                <td>{{ $row['id'] }}</td>
                <td>{{ $row['name'] }}</td>
                <td>{{ $row['slug'] }}</td>
                <td>{{ $row['target_name'] ?? '-' }}</td>
                <td style="width: 110px;">
                  <form action="{{ route('backend.categories.quick-update-sort', $row['id']) }}" method="POST" class="mb-0 js-category-sort-form">
                    @csrf
                    @method('PATCH')
                    <input type="number" name="sort_order" class="form-control form-control-sm js-category-sort-input" min="0" value="{{ $row['sort_order'] }}">
                  </form>
                </td>
                <td>{{ $row['status'] === 'active' ? 'Hoạt động' : 'Ngừng hoạt động' }}</td>
                <td>{{ $row['updated_at'] }}</td>
                <td>
                  <a href="{{ route('backend.categories.show', $row['id']) }}" class="btn btn-info btn-sm text-white">Xem</a>
                  <a href="{{ route('backend.categories.edit', $row['id']) }}" class="btn btn-outline-primary btn-sm">Sửa</a>
                  <form action="{{ route('backend.categories.destroy', $row['id']) }}" method="POST" class="d-inline" onsubmit="return confirm('Bạn có chắc muốn xóa danh mục này?')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-outline-danger btn-sm">Xóa</button>
                  </form>
                </td>
              </tr>
            @empty
              <tr>
                <td colspan="8" class="text-center">Chưa có danh mục cha.</td>
              </tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </div>

    <div class="card mb-3">
      <div class="card-header">
        <h3 class="card-title mb-0">Danh mục con (Cấp 2)</h3>
      </div>
      <div class="card-body">
        <table class="table table-striped table-hover align-middle mb-0">
          <thead>
            <tr>
              <th>ID</th>
              <th>Thuộc danh mục cha</th>
              <th>Tên</th>
              <th>Slug</th>
              <th>Đối tượng</th>
              <th>Thứ tự</th>
              <th>Trạng thái</th>
              <th>Cập nhật lúc</th>
              <th>Thao tác</th>
            </tr>
          </thead>
          <tbody>
            @forelse ($childCategories as $row)
              <tr>
                <td>{{ $row['id'] }}</td>
                <td>{{ explode(' > ', $row['path'])[0] ?? '-' }}</td>
                <td>{{ $row['name'] }}</td>
                <td>{{ $row['slug'] }}</td>
                <td>{{ $row['target_name'] ?? '-' }}</td>
                <td style="width: 110px;">
                  <form action="{{ route('backend.categories.quick-update-sort', $row['id']) }}" method="POST" class="mb-0 js-category-sort-form">
                    @csrf
                    @method('PATCH')
                    <input type="number" name="sort_order" class="form-control form-control-sm js-category-sort-input" min="0" value="{{ $row['sort_order'] }}">
                  </form>
                </td>
                <td>{{ $row['status'] === 'active' ? 'Hoạt động' : 'Ngừng hoạt động' }}</td>
                <td>{{ $row['updated_at'] }}</td>
                <td>
                  <a href="{{ route('backend.categories.show', $row['id']) }}" class="btn btn-info btn-sm text-white">Xem</a>
                  <a href="{{ route('backend.categories.edit', $row['id']) }}" class="btn btn-outline-primary btn-sm">Sửa</a>
                  <form action="{{ route('backend.categories.destroy', $row['id']) }}" method="POST" class="d-inline" onsubmit="return confirm('Bạn có chắc muốn xóa danh mục này?')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-outline-danger btn-sm">Xóa</button>
                  </form>
                </td>
              </tr>
            @empty
              <tr>
                <td colspan="9" class="text-center">Chưa có danh mục con.</td>
              </tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </div>

    <div class="card">
      <div class="card-header">
        <h3 class="card-title mb-0">Danh mục cháu (Cấp 3)</h3>
      </div>
      <div class="card-body">
        <table class="table table-striped table-hover align-middle mb-0">
          <thead>
            <tr>
              <th>ID</th>
              <th>Danh mục cha</th>
              <th>Danh mục con</th>
              <th>Tên cháu</th>
              <th>Slug</th>
              <th>Đối tượng</th>
              <th>Thứ tự</th>
              <th>Trạng thái</th>
              <th>Cập nhật lúc</th>
              <th>Thao tác</th>
            </tr>
          </thead>
          <tbody>
            @forelse ($grandchildCategories as $row)
              @php
                $parts = explode(' > ', $row['path']);
              @endphp
              <tr>
                <td>{{ $row['id'] }}</td>
                <td>{{ $parts[0] ?? '-' }}</td>
                <td>{{ $parts[1] ?? '-' }}</td>
                <td>{{ $row['name'] }}</td>
                <td>{{ $row['slug'] }}</td>
                <td>{{ $row['target_name'] ?? '-' }}</td>
                <td style="width: 110px;">
                  <form action="{{ route('backend.categories.quick-update-sort', $row['id']) }}" method="POST" class="mb-0 js-category-sort-form">
                    @csrf
                    @method('PATCH')
                    <input type="number" name="sort_order" class="form-control form-control-sm js-category-sort-input" min="0" value="{{ $row['sort_order'] }}">
                  </form>
                </td>
                <td>{{ $row['status'] === 'active' ? 'Hoạt động' : 'Ngừng hoạt động' }}</td>
                <td>{{ $row['updated_at'] }}</td>
                <td>
                  <a href="{{ route('backend.categories.show', $row['id']) }}" class="btn btn-info btn-sm text-white">Xem</a>
                  <a href="{{ route('backend.categories.edit', $row['id']) }}" class="btn btn-outline-primary btn-sm">Sửa</a>
                  <form action="{{ route('backend.categories.destroy', $row['id']) }}" method="POST" class="d-inline" onsubmit="return confirm('Bạn có chắc muốn xóa danh mục này?')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-outline-danger btn-sm">Xóa</button>
                  </form>
                </td>
              </tr>
            @empty
              <tr>
                <td colspan="10" class="text-center">Chưa có danh mục cháu.</td>
              </tr>
            @endforelse
          </tbody>
        </table>
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

    const submitCategorySort = function (input) {
      const form = input.closest('.js-category-sort-form');
      if (!form) {
        return;
      }

      const nextValue = input.value.trim();
      const originalValue = input.defaultValue;
      if (nextValue === '') {
        input.value = originalValue;
        showAlert('danger', 'Số thứ tự không được để trống.');
        return;
      }

      if (nextValue === originalValue) {
        return;
      }

      const $form = window.jQuery(form);
      const tokenInput = form.querySelector('input[name="_token"]');
      const methodInput = form.querySelector('input[name="_method"]');
      const token = tokenInput ? tokenInput.value : '';
      const method = methodInput ? methodInput.value : 'PATCH';
      input.disabled = true;

      window.jQuery.ajax({
        url: $form.attr('action'),
        type: 'POST',
        data: {
          _token: token,
          _method: method,
          sort_order: nextValue
        },
        headers: {
          'Accept': 'application/json',
          'X-Requested-With': 'XMLHttpRequest'
        }
      }).done(function (result) {
        if (!result || !result.success) {
          throw new Error((result && result.message) || 'Cập nhật thất bại.');
        }

        input.value = result.sort_order;
        input.defaultValue = String(result.sort_order);
        showAlert('success', result.message || 'Đã cập nhật số thứ tự.');
      }).fail(function (xhr) {
        const message = xhr.responseJSON && xhr.responseJSON.message
          ? xhr.responseJSON.message
          : 'Có lỗi xảy ra khi cập nhật.';

        input.value = originalValue;
        showAlert('danger', message);
      }).always(function () {
        input.disabled = false;
      });
    };

    window.jQuery(document)
      .off('blur.categorySort')
      .on('blur.categorySort', '.js-category-sort-input', function () {
        submitCategorySort(this);
      })
      .off('keydown.categorySort')
      .on('keydown.categorySort', '.js-category-sort-input', function (event) {
        if (event.key !== 'Enter') {
          return;
        }

        event.preventDefault();
        this.blur();
      });
  });
</script>
@endpush
