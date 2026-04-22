<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>@yield('title', 'Bảng điều khiển quản trị')</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" />
  <link rel="stylesheet" href="{{ asset('backend/AdminLTE/css/adminlte.min.css') }}" />
  @hasSection('use_datatable')
    <link rel="stylesheet" href="{{ asset('backend/datatable/datatables.min.css') }}" />
  @endif
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" />
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" />
  <link rel="stylesheet" href="{{ asset('backend/admin-custom.css') }}" />
  <style>
    html, body, .app-wrapper { margin: 0 !important; padding: 0 !important; }
    .app-header { margin-top: 0 !important; }
    table.dataTable { border-collapse: separate !important; }
    .app-content table td.cell-ellipsis {
      max-width: 240px;
      white-space: nowrap;
      overflow: hidden;
      text-overflow: ellipsis;
    }
  </style>
</head>
<body class="layout-fixed sidebar-expand-lg bg-body-tertiary">
  @php
    $backendUser = auth()->user();
    $isAdmin = $backendUser?->role === 'admin';
    $canAccess = fn (string $permission): bool => $isAdmin || (bool) $backendUser?->hasBackendPermission($permission);
    $backendHomeRouteName = $isAdmin ? 'backend.index' : ($backendUser?->defaultBackendRouteName() ?? 'backend.login');
    $backendHomeRoute = route($backendHomeRouteName);
  @endphp
  <div class="app-wrapper">
    <nav class="app-header navbar navbar-expand bg-body">
      <div class="container-fluid">
        <ul class="navbar-nav">
          <li class="nav-item"><a class="nav-link" data-lte-toggle="sidebar" href="#" role="button"><i class="bi bi-list"></i></a></li>
          <li class="nav-item d-none d-md-block"><a href="{{ url('/') }}" class="nav-link">Xem giao diện người dùng</a></li>
        </ul>
        <ul class="navbar-nav ms-auto">
          <li class="nav-item d-none d-md-flex align-items-center me-2 text-secondary small">
            {{ auth()->user()?->full_name ?: auth()->user()?->email }}
          </li>
          <li class="nav-item">
            <form method="POST" action="{{ route('backend.logout') }}" class="mb-0">
              @csrf
              <button type="submit" class="nav-link border-0 bg-transparent">
                <i class="bi bi-box-arrow-right"></i>
              </button>
            </form>
          </li>
        </ul>
      </div>
    </nav>

    <aside class="app-sidebar bg-body-secondary shadow" data-bs-theme="dark">
      <div class="sidebar-brand">
        <a href="{{ $backendHomeRoute }}" class="brand-link">
          <img src="{{ asset('backend/AdminLTE/assets/img/AdminLTELogo.png') }}" alt="Logo" class="brand-image opacity-75 shadow" />
          <span class="brand-text fw-light">Quản trị hệ thống</span>
        </a>
      </div>
      <div class="sidebar-wrapper">
        <nav class="mt-2">
          <ul class="nav sidebar-menu flex-column" data-lte-toggle="treeview" role="navigation" data-accordion="false">
            @if ($canAccess('dashboard'))
              <li class="nav-item"><a href="{{ route('backend.index') }}" class="nav-link {{ request()->routeIs('backend.index') ? 'active' : '' }}"><i class="nav-icon bi bi-grid-1x2-fill"></i><p>Bảng điều khiển</p></a></li>
            @endif
            @if ($canAccess('banners'))
              <li class="nav-item"><a href="{{ route('backend.banners') }}" class="nav-link {{ request()->routeIs('backend.banners*') ? 'active' : '' }}"><i class="nav-icon bi bi-images"></i><p>Banner</p></a></li>
            @endif
            @if ($canAccess('home_management'))
              <li class="nav-item"><a href="{{ route('backend.home-management') }}" class="nav-link {{ request()->routeIs('backend.home-management*') ? 'active' : '' }}"><i class="nav-icon bi bi-window-stack"></i><p>Quản lý trang chủ</p></a></li>
            @endif
            @if ($canAccess('categories') || $canAccess('product_targets'))
              <li class="nav-item {{ request()->routeIs('backend.categories*') || request()->routeIs('backend.product-targets*') ? 'menu-open' : '' }}">
                <a href="#" class="nav-link {{ request()->routeIs('backend.categories*') || request()->routeIs('backend.product-targets*') ? 'active' : '' }}">
                  <i class="nav-icon bi bi-diagram-3"></i>
                  <p>Danh mục<i class="nav-arrow bi bi-chevron-right"></i></p>
                </a>
                <ul class="nav nav-treeview">
                  @if ($canAccess('categories'))
                    <li class="nav-item"><a href="{{ route('backend.categories') }}" class="nav-link {{ request()->routeIs('backend.categories*') ? 'active' : '' }}"><i class="nav-icon bi bi-circle"></i><p>Danh mục</p></a></li>
                  @endif
                  @if ($canAccess('product_targets'))
                    <li class="nav-item"><a href="{{ route('backend.product-targets') }}" class="nav-link {{ request()->routeIs('backend.product-targets*') ? 'active' : '' }}"><i class="nav-icon bi bi-circle"></i><p>Đối tượng danh mục</p></a></li>
                  @endif
                </ul>
              </li>
            @endif
            @if ($canAccess('products') || $canAccess('product_colors') || $canAccess('product_sizes') || $canAccess('product_tags'))
              <li class="nav-item {{ request()->routeIs('backend.products*') || request()->routeIs('backend.product-colors*') || request()->routeIs('backend.product-sizes*') || request()->routeIs('backend.product-tags*') ? 'menu-open' : '' }}">
                <a href="#" class="nav-link {{ request()->routeIs('backend.products*') || request()->routeIs('backend.product-colors*') || request()->routeIs('backend.product-sizes*') || request()->routeIs('backend.product-tags*') ? 'active' : '' }}">
                  <i class="nav-icon bi bi-box-seam"></i>
                  <p>Sản phẩm<i class="nav-arrow bi bi-chevron-right"></i></p>
                </a>
                <ul class="nav nav-treeview">
                  @if ($canAccess('products'))
                    <li class="nav-item"><a href="{{ route('backend.products') }}" class="nav-link {{ request()->routeIs('backend.products*') ? 'active' : '' }}"><i class="nav-icon bi bi-circle"></i><p>Danh sách sản phẩm</p></a></li>
                  @endif
                  @if ($canAccess('product_colors'))
                    <li class="nav-item"><a href="{{ route('backend.product-colors') }}" class="nav-link {{ request()->routeIs('backend.product-colors*') ? 'active' : '' }}"><i class="nav-icon bi bi-circle"></i><p>Màu sắc sản phẩm</p></a></li>
                  @endif
                  @if ($canAccess('product_sizes'))
                    <li class="nav-item"><a href="{{ route('backend.product-sizes') }}" class="nav-link {{ request()->routeIs('backend.product-sizes*') ? 'active' : '' }}"><i class="nav-icon bi bi-circle"></i><p>Kích thước sản phẩm</p></a></li>
                  @endif
                  @if ($canAccess('product_tags'))
                    <li class="nav-item"><a href="{{ route('backend.product-tags') }}" class="nav-link {{ request()->routeIs('backend.product-tags*') ? 'active' : '' }}"><i class="nav-icon bi bi-circle"></i><p>Tag sản phẩm</p></a></li>
                  @endif
                </ul>
              </li>
            @endif
            @if ($canAccess('promotions'))
              <li class="nav-item"><a href="{{ route('backend.promotions') }}" class="nav-link {{ request()->routeIs('backend.promotions*') ? 'active' : '' }}"><i class="nav-icon bi bi-megaphone"></i><p>Khuyến mãi</p></a></li>
            @endif
            @if ($canAccess('promo_tickers'))
              <li class="nav-item"><a href="{{ route('backend.promo-tickers') }}" class="nav-link {{ request()->routeIs('backend.promo-tickers*') ? 'active' : '' }}"><i class="nav-icon bi bi-broadcast-pin"></i><p>Promo Ticker</p></a></li>
            @endif
            @if ($canAccess('footer_links'))
              <li class="nav-item"><a href="{{ route('backend.footer-links') }}" class="nav-link {{ request()->routeIs('backend.footer-links*') ? 'active' : '' }}"><i class="nav-icon bi bi-link-45deg"></i><p>Link chân trang</p></a></li>
            @endif
            @if ($canAccess('orders'))
              <li class="nav-item"><a href="{{ route('backend.orders') }}" class="nav-link {{ request()->routeIs('backend.orders*') ? 'active' : '' }}"><i class="nav-icon bi bi-receipt"></i><p>Đơn hàng</p></a></li>
            @endif
            @if ($canAccess('payment_invoices'))
              <li class="nav-item"><a href="{{ route('backend.payment-invoices') }}" class="nav-link {{ request()->routeIs('backend.payment-invoices*') ? 'active' : '' }}"><i class="nav-icon bi bi-receipt-cutoff"></i><p>Hóa đơn</p></a></li>
            @endif
            @if ($canAccess('sepay_webhook_logs'))
              <li class="nav-item"><a href="{{ route('backend.sepay-webhook-logs') }}" class="nav-link {{ request()->routeIs('backend.sepay-webhook-logs*') ? 'active' : '' }}"><i class="nav-icon bi bi-bug"></i><p>Nhật ký SePay</p></a></li>
            @endif
            @if ($canAccess('customers') || $canAccess('customers_ranking') || $canAccess('customers_config'))
              <li class="nav-item {{ request()->routeIs('backend.customers*') ? 'menu-open' : '' }}">
                <a href="#" class="nav-link {{ request()->routeIs('backend.customers*') ? 'active' : '' }}">
                  <i class="nav-icon bi bi-people"></i>
                  <p>Khách hàng<i class="nav-arrow bi bi-chevron-right"></i></p>
                </a>
                <ul class="nav nav-treeview">
                  @if ($canAccess('customers'))
                    <li class="nav-item"><a href="{{ route('backend.customers') }}" class="nav-link {{ request()->routeIs('backend.customers') || request()->routeIs('backend.customers.show') || request()->routeIs('backend.customers.edit') || request()->routeIs('backend.customers.create') ? 'active' : '' }}"><i class="nav-icon bi bi-circle"></i><p>Danh sách khách hàng</p></a></li>
                  @endif
                  @if ($canAccess('customers_ranking'))
                    <li class="nav-item"><a href="{{ route('backend.customers.ranking') }}" class="nav-link {{ request()->routeIs('backend.customers.ranking') ? 'active' : '' }}"><i class="nav-icon bi bi-circle"></i><p>Xếp hạng mua hàng</p></a></li>
                  @endif
                  @if ($canAccess('customers_config'))
                    <li class="nav-item"><a href="{{ route('backend.customers.config') }}" class="nav-link {{ request()->routeIs('backend.customers.config*') ? 'active' : '' }}"><i class="nav-icon bi bi-circle"></i><p>Cấu hình rank</p></a></li>
                  @endif
                </ul>
              </li>
            @endif
            @if ($canAccess('stores'))
              <li class="nav-item"><a href="{{ route('backend.stores') }}" class="nav-link {{ request()->routeIs('backend.stores*') ? 'active' : '' }}"><i class="nav-icon bi bi-shop"></i><p>Cửa hàng & nội dung</p></a></li>
            @endif
            @if ($canAccess('settings'))
              <li class="nav-item"><a href="{{ route('backend.settings') }}" class="nav-link {{ request()->routeIs('backend.settings*') ? 'active' : '' }}"><i class="nav-icon bi bi-gear"></i><p>Cấu hình website</p></a></li>
            @endif
            @if ($canAccess('activity_logs'))
              <li class="nav-item"><a href="{{ route('backend.activity-logs') }}" class="nav-link {{ request()->routeIs('backend.activity-logs*') ? 'active' : '' }}"><i class="nav-icon bi bi-clock-history"></i><p>Nhật ký hoạt động</p></a></li>
            @endif
            @if ($isAdmin)
              <li class="nav-item"><a href="{{ route('backend.roles') }}" class="nav-link {{ request()->routeIs('backend.roles*') ? 'active' : '' }}"><i class="nav-icon bi bi-shield-lock"></i><p>Quản lý role</p></a></li>
            @endif
          </ul>
        </nav>
      </div>
    </aside>

    <main class="app-main">
      @yield('content')
    </main>

    <footer class="app-footer">
      <strong>Demo quản trị</strong> xây dựng với AdminLTE.
      <div class="float-end d-none d-sm-inline">v1.1</div>
    </footer>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <script src="{{ asset('backend/AdminLTE/js/adminlte.min.js') }}"></script>
  <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
  @hasSection('use_datatable')
    <script src="{{ asset('backend/datatable/datatables.min.js') }}"></script>
  @endif
  <script src="{{ asset('backend/admin-functions.js') }}"></script>
  <script>
    document.addEventListener('DOMContentLoaded', function () {
      if (typeof window.jQuery !== 'undefined' && typeof window.jQuery.fn.select2 !== 'undefined') {
        window.jQuery('.js-multi-select').each(function () {
          const $el = window.jQuery(this);
          $el.select2({
            theme: 'bootstrap-5',
            width: '100%',
            closeOnSelect: false,
            placeholder: $el.data('placeholder') || ''
          });
        });
      }

      if (typeof window.DataTable === 'undefined' || typeof window.jQuery === 'undefined') return;

      function applyCellEllipsis(table) {
        table.querySelectorAll('tbody tr').forEach(function (row) {
          row.querySelectorAll('td').forEach(function (cell) {
            if (cell.querySelector('a.btn')) return;
            cell.classList.add('cell-ellipsis');
            cell.title = cell.textContent.trim();
          });
        });
      }

      document.querySelectorAll('table.js-datatable').forEach(function (table) {
        if (!table.querySelector('thead') || !table.querySelector('tbody')) return;
        if (table.dataset.datatableInitialized === '1') return;
        table.dataset.datatableInitialized = '1';
        table.classList.remove('table', 'table-striped', 'table-bordered', 'mb-0');
        table.classList.add('display');
        let configuredOrder = [];
        if (table.dataset.order) {
          try {
            configuredOrder = JSON.parse(table.dataset.order);
          } catch (error) {
            configuredOrder = [];
          }
        }
        const dataTable = new window.DataTable(table, {
          pageLength: 10,
          lengthMenu: [10, 25, 50, 100],
          order: configuredOrder,
          responsive: true,
          language: {
            search: 'Tìm kiếm:',
            lengthMenu: 'Hiển thị _MENU_ dòng',
            info: 'Hiển thị từ _START_ đến _END_ trên tổng _TOTAL_ dòng',
            infoEmpty: 'Không có dữ liệu',
            zeroRecords: 'Không tìm thấy bản ghi phù hợp',
            paginate: { first: 'Đầu', last: 'Cuối', next: 'Sau', previous: 'Trước' }
          }
        });

        applyCellEllipsis(table);
        if (typeof dataTable.on === 'function') {
          dataTable.on('draw', function () {
            applyCellEllipsis(table);
          });
        }
      });

      function ensureAjaxDeleteAlertHost() {
        let host = document.getElementById('ajax-delete-alert');
        if (host) return host;

        const container = document.querySelector('.app-content .container-fluid');
        if (!container) return null;

        host = document.createElement('div');
        host.id = 'ajax-delete-alert';
        container.prepend(host);

        return host;
      }

      function showAjaxDeleteAlert(type, message) {
        const host = ensureAjaxDeleteAlertHost();
        if (!host) return;

        host.innerHTML = `<div class="alert alert-${type} alert-dismissible fade show" role="alert">
          ${message}
          <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>`;
      }

      function getDeleteConfirmMessage(form) {
        const existing = form.dataset.confirmMessage;
        if (existing) return existing;

        const inlineHandler = form.getAttribute('onsubmit') || '';
        const match = inlineHandler.match(/confirm\((['"])(.*?)\1\)/);
        if (match && match[2]) {
          form.dataset.confirmMessage = match[2];
        }

        form.removeAttribute('onsubmit');

        return form.dataset.confirmMessage || 'Xóa mục này?';
      }

      document.querySelectorAll('form').forEach(function (form) {
        if (form.querySelector('input[name="_method"][value="DELETE"]')) {
          getDeleteConfirmMessage(form);
        }
      });

      window.jQuery(document)
        .off('submit.ajaxDelete')
        .on('submit.ajaxDelete', 'form', function (event) {
          const form = event.currentTarget;
          if (!form.querySelector('input[name="_method"][value="DELETE"]')) return;

          event.preventDefault();

          const confirmMessage = getDeleteConfirmMessage(form);
          if (!window.confirm(confirmMessage)) {
            return;
          }

          const $form = window.jQuery(form);
          const $row = $form.closest('tr');
          const $table = $row.closest('table');
          const hasDataTable = $table.length && window.jQuery.fn.DataTable && window.jQuery.fn.DataTable.isDataTable($table[0]);
          const dataTable = hasDataTable ? $table.DataTable() : null;
          const $button = $form.find('button[type="submit"]').first();
          const originalText = $button.text();

          $button.prop('disabled', true).text('Đang xóa...');

          const hideTarget = $row.length ? $row : $form;
          hideTarget.stop(true, true).fadeOut(180, function () {
            window.jQuery.ajax({
              url: $form.attr('action'),
              type: 'POST',
              data: $form.serialize(),
              headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
              }
            }).done(function (result) {
              if (!result || !result.success) {
                hideTarget.stop(true, true).fadeIn(160);
                showAjaxDeleteAlert('danger', (result && result.message) || 'Xóa thất bại.');
                return;
              }

              if ($row.length && dataTable) {
                dataTable.row($row[0]).remove().draw(false);
              } else if ($row.length) {
                $row.remove();
              } else {
                $form.remove();
              }

              showAjaxDeleteAlert('success', result.message || 'Đã xóa thành công.');
            }).fail(function (xhr) {
              const message = xhr.responseJSON && xhr.responseJSON.message
                ? xhr.responseJSON.message
                : 'Có lỗi xảy ra khi xóa.';

              hideTarget.stop(true, true).fadeIn(160);
              showAjaxDeleteAlert('danger', message);
            }).always(function () {
              $button.prop('disabled', false).text(originalText);
            });
          });
        });
    });
  </script>
  @stack('scripts')
</body>
</html>
