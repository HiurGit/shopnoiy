@extends('backend.layouts.app')

@section('title', 'Đơn hàng')
@section('use_datatable', true)

@push('styles')
<style>
  .order-note-flag {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    margin-top: 6px;
    padding: 4px 10px;
    border-radius: 999px;
    background: #fff3cd;
    color: #8a5200;
    font-size: 12px;
    font-weight: 700;
    box-shadow: inset 0 0 0 1px rgba(138, 82, 0, 0.14);
  }

  .order-note-flag.is-empty {
    background: #f1f3f5;
    color: #6c757d;
    box-shadow: inset 0 0 0 1px rgba(108, 117, 125, 0.14);
  }

</style>
@endpush

@section('content')
<div class="app-content-header">
  <div class="container-fluid">
    <h1 class="mb-0">Đơn hàng</h1>
  </div>
</div>

<div class="app-content">
  <div class="container-fluid">
    @if (session('success'))
      <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="card">
      <div class="card-header d-flex justify-content-between align-items-center">
        <h3 class="card-title mb-0">Danh sách đơn hàng</h3>
        <a href="{{ route('backend.orders.create') }}" class="btn btn-primary btn-sm ms-auto">Thêm mới</a>
      </div>

      <div class="card-body">
        <div class="table-responsive">
          <table class="table table-striped table-bordered align-middle mb-0 js-datatable" data-order='[[0, "desc"]]'>
            <thead>
              <tr>
                <th>ID</th>
                <th>Mã đơn</th>
                <th>Khách hàng</th>
                <th>Số điện thoại</th>
                <th>Ghi chú</th>
                <th>Trạng thái</th>
                <th>Giờ xác minh</th>
                <th>Tổng</th>
                <th>Ngày tạo</th>
                <th>Thao tác</th>
              </tr>
            </thead>
            <tbody>
              @forelse ($orders as $order)
                <tr>
                  <td>{{ $order->id }}</td>
                  <td>{{ $order->order_code }}</td>
                  <td><div class="fw-semibold">{{ $order->customer_name }}</div></td>
                  <td>
                    @if ($order->user_id && $order->customer_phone)
                      <a href="{{ route('backend.customers.show', $order->user_id) }}" class="fw-semibold text-decoration-none">{{ $order->customer_phone }}</a>
                    @else
                      {{ $order->customer_phone }}
                    @endif
                  </td>
                  <td>
                    @if (filled($order->note))
                      <div class="order-note-flag" title="{{ $order->note }}">
                        <i class="bi bi-stickies-fill"></i>
                        <span>Có ghi chú</span>
                      </div>
                    @else
                      <div class="order-note-flag is-empty">
                        <i class="bi bi-dash-circle"></i>
                        <span>Không</span>
                      </div>
                    @endif
                  </td>
                  <td>{{ $order->order_status_label }}</td>
                  <td>{{ optional($order->verified_at)->format('d/m/Y H:i') ?: '-' }}</td>
                  <td>{{ number_format((float) $order->total_amount, 0, ',', '.') }}đ</td>
                  <td>{{ optional($order->created_at)->format('d/m/Y H:i') }}</td>
                  <td>
                    @if ($order->isPendingVerification())
                      <form action="{{ route('backend.orders.mark-verified', $order) }}" method="POST" class="d-inline" onsubmit="return confirm('Xác minh đơn hàng này?')">
                        @csrf
                        @method('PATCH')
                        <button class="btn btn-success btn-sm" type="submit">Đã xác minh</button>
                      </form>
                    @endif
                    <a href="{{ route('backend.orders.show', $order) }}" class="btn btn-info btn-sm text-white">Xem</a>
                    <a href="{{ route('backend.orders.edit', $order) }}" class="btn btn-outline-primary btn-sm">Sửa</a>
                    <form action="{{ route('backend.orders.destroy', $order) }}" method="POST" class="d-inline" onsubmit="return confirm('Xóa đơn hàng này?')">
                      @csrf
                      @method('DELETE')
                      <button class="btn btn-outline-danger btn-sm" type="submit">Xóa</button>
                    </form>
                  </td>
                </tr>
              @empty
                <tr>
                  <td colspan="10" class="text-center">Chưa có dữ liệu.</td>
                </tr>
              @endforelse
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</div>
@endsection
