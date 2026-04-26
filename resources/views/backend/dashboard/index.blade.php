@extends('backend.layouts.app')

@section('title', 'Bảng điều khiển')

@section('content')
<div class="app-content-header">
  <div class="container-fluid d-flex justify-content-between align-items-center flex-wrap gap-2">
    <div>
      <h1 class="mb-0">Bảng điều khiển</h1>
      <p class="text-secondary mb-0">Theo dõi nhanh tình hình bán hàng trong ngày.</p>
    </div>
    <a href="{{ route('backend.orders') }}" class="btn btn-primary btn-sm">Xem tất cả đơn hàng</a>
  </div>
</div>

<div class="app-content">
  <div class="container-fluid">
    <div class="row g-3 mb-3">
      <div class="col-12 col-md-6 col-xl-3">
        <div class="card h-100">
          <div class="card-body">
            <p class="text-secondary mb-1">Tổng đơn hàng</p>
            <h3 class="mb-0">{{ number_format($kpis['total_orders']) }}</h3>
          </div>
        </div>
      </div>
      <div class="col-12 col-md-6 col-xl-3">
        <div class="card h-100">
          <div class="card-body">
            <p class="text-secondary mb-1">Đơn hàng hôm nay</p>
            <h3 class="mb-0">{{ number_format($kpis['today_orders']) }}</h3>
          </div>
        </div>
      </div>
      <div class="col-12 col-md-6 col-xl-3">
        <div class="card h-100 border-success-subtle">
          <div class="card-body">
            <p class="text-secondary mb-1">Doanh thu hôm nay</p>
            <h3 class="mb-0 text-success">{{ number_format($kpis['today_revenue'], 0, ',', '.') }}đ</h3>
          </div>
        </div>
      </div>
      <div class="col-12 col-md-6 col-xl-3">
        <div class="card h-100 border-info-subtle">
          <div class="card-body">
            <p class="text-secondary mb-1">Doanh thu đã thanh toán hôm nay</p>
            <h3 class="mb-0 text-info">{{ number_format($kpis['today_paid_revenue'], 0, ',', '.') }}đ</h3>
          </div>
        </div>
      </div>
    </div>

    <div class="row g-3 mb-3">
      <div class="col-12 col-md-6 col-xl-3">
        <div class="card h-100">
          <div class="card-body">
            <p class="text-secondary mb-1">Đơn chờ xử lý</p>
            <h4 class="mb-0">{{ number_format($kpis['pending_orders']) }}</h4>
          </div>
        </div>
      </div>
      <div class="col-12 col-md-6 col-xl-3">
        <div class="card h-100">
          <div class="card-body">
            <p class="text-secondary mb-1">Tổng khách hàng</p>
            <h4 class="mb-0">{{ number_format($kpis['total_customers']) }}</h4>
          </div>
        </div>
      </div>
      <div class="col-12 col-md-6 col-xl-3">
        <div class="card h-100">
          <div class="card-body">
            <p class="text-secondary mb-1">Tổng sản phẩm</p>
            <h4 class="mb-0">{{ number_format($kpis['total_products']) }}</h4>
          </div>
        </div>
      </div>
      <div class="col-12 col-md-6 col-xl-3">
        <div class="card h-100">
          <div class="card-body">
            <p class="text-secondary mb-1">Sản phẩm sắp hết hàng (<= 5)</p>
            <h4 class="mb-0">{{ number_format($kpis['low_stock_products']) }}</h4>
          </div>
        </div>
      </div>
    </div>

    <div class="row g-3">
      <div class="col-12 col-xl-8">
        <div class="card h-100">
          <div class="card-header d-flex justify-content-between align-items-center">
            <h3 class="card-title mb-0">Đơn hàng mới nhất</h3>
            <a href="{{ route('backend.orders') }}" class="btn btn-outline-primary btn-sm">Đi tới quản lý đơn hàng</a>
          </div>
          <div class="card-body">
            <div class="table-responsive">
              <table class="table table-striped table-hover align-middle mb-0">
                <thead>
                  <tr>
                    <th>ID</th>
                    <th>Mã đơn</th>
                    <th>Khách hàng</th>
                    <th>Trạng thái</th>
                    <th>Thanh toán</th>
                    <th>Tổng tiền</th>
                    <th>Ngày tạo</th>
                    <th>Thao tác</th>
                  </tr>
                </thead>
                <tbody>
                  @forelse ($recentOrders as $order)
                    @php
                      $dashboardPaymentMethod = strtolower((string) ($order->payment_method ?? ''));
                      $dashboardPaymentStatus = strtolower((string) ($order->payment_status ?? 'unpaid'));
                      $dashboardPaymentStatusLabel = $dashboardPaymentMethod === 'cod' && in_array($dashboardPaymentStatus, ['unpaid', 'pending'], true)
                          ? 'Thanh toán khi nhận hàng'
                          : $dashboardPaymentStatus;
                    @endphp
                    <tr>
                      <td>{{ $order->id }}</td>
                      <td>{{ $order->order_code }}</td>
                      <td>{{ $order->customer_name }}</td>
                      <td>
                        @if ($order->order_status === 'pending_verification')
                          <span class="badge rounded-pill px-3 py-2 bg-warning-subtle text-warning-emphasis border border-warning-subtle">
                            {{ $order->order_status_label }}
                          </span>
                        @else
                          {{ $order->order_status_label }}
                        @endif
                      </td>
                      <td>{{ $dashboardPaymentStatusLabel }}</td>
                      <td>{{ number_format((float) $order->total_amount, 0, ',', '.') }}đ</td>
                      <td>{{ optional($order->created_at)->format('d/m/Y H:i') }}</td>
                      <td><a href="{{ route('backend.orders.show', $order) }}" class="btn btn-info btn-sm text-white">Xem</a></td>
                    </tr>
                  @empty
                    <tr><td colspan="8" class="text-center">Chưa có đơn hàng.</td></tr>
                  @endforelse
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>

      <div class="col-12 col-xl-4">
        <div class="card mb-3">
          <div class="card-header">
            <h3 class="card-title mb-0">Doanh thu 7 ngày gần nhất</h3>
          </div>
          <div class="card-body p-0">
            <ul class="list-group list-group-flush">
              @foreach ($dailyRevenue as $item)
                <li class="list-group-item d-flex justify-content-between align-items-center">
                  <span>{{ \Illuminate\Support\Carbon::parse($item['date'])->format('d/m') }}</span>
                  <span class="small text-secondary">{{ $item['orders_count'] }} đơn</span>
                  <strong>{{ number_format((float) $item['revenue'], 0, ',', '.') }}đ</strong>
                </li>
              @endforeach
            </ul>
          </div>
        </div>

        <div class="card">
          <div class="card-header">
            <h3 class="card-title mb-0">Gợi ý mở rộng dashboard</h3>
          </div>
          <div class="card-body">
            <ul class="mb-0 ps-3">
              <li>Tỷ lệ chuyển đổi từ giỏ hàng sang đơn hàng.</li>
              <li>Top 5 sản phẩm bán chạy trong tuần.</li>
              <li>Đơn có nguy cơ hủy (chờ xử lý quá lâu).</li>
              <li>Hiệu quả khuyến mãi theo mã code.</li>
            </ul>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
@endsection
