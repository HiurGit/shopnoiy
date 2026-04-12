@extends('backend.layouts.app')
@section('title','Chi tiết khách hàng')
@section('content')
@php
  $tierLabels = [
      'new' => 'Khách hàng mới',
      'friendly' => 'Khách hàng thân thiện',
      'loyal' => 'Khách hàng trung thành',
      'vip' => 'Khách hàng VIP',
      'diamond' => 'Khách hàng Kim cương',
  ];
@endphp
<div class="app-content-header">
  <div class="container-fluid d-flex justify-content-between align-items-center flex-wrap gap-2">
    <h1 class="mb-0">Chi tiết khách hàng</h1>
    <a href="{{ route('backend.customers') }}" class="btn btn-secondary btn-sm">Quay lại</a>
  </div>
</div>
<div class="app-content">
  <div class="container-fluid">
    <div class="row g-3">
      <div class="col-lg-5">
        <div class="card">
          <div class="card-header">
            <h3 class="card-title mb-0">Hồ sơ khách hàng</h3>
          </div>
          <div class="card-body">
            <table class="table table-bordered align-middle mb-0">
              <tbody>
                <tr><th style="width:220px;">Họ tên</th><td>{{ $customer->full_name }}</td></tr>
                <tr><th>Email</th><td>{{ $customer->email }}</td></tr>
                <tr><th>Điện thoại</th><td>{{ $customer->phone }}</td></tr>
                <tr><th>Rank</th><td><span class="badge bg-warning-subtle text-warning-emphasis">{{ $tierLabels[$profile->tier ?? 'new'] ?? ($profile->tier ?? 'Khách hàng mới') }}</span></td></tr>
                <tr><th>Tổng chi</th><td class="fw-bold text-primary">{{ number_format((float) ($profile->total_spent ?? 0), 0, ',', '.') }}đ</td></tr>
                <tr><th>Tổng đơn</th><td>{{ number_format((int) ($profile->total_orders ?? 0)) }}</td></tr>
                <tr><th>Trạng thái</th><td>{{ $customer->status }}</td></tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>
      <div class="col-lg-7">
        <div class="card">
          <div class="card-header d-flex justify-content-between align-items-center">
            <h3 class="card-title mb-0">Dòng thời gian mua hàng</h3>
            <span class="small text-muted">{{ $orders->count() }} đơn gần nhất</span>
          </div>
          <div class="card-body p-0">
            <div class="table-responsive">
              <table class="table table-striped align-middle mb-0">
                <thead>
                  <tr>
                    <th>ID</th>
                    <th>Mã đơn</th>
                    <th>Ngày mua</th>
                    <th>Xác minh</th>
                    <th>Tổng tiền</th>
                    <th>Trạng thái</th>
                    <th>Thao tác</th>
                  </tr>
                </thead>
                <tbody>
                  @forelse($orders as $o)
                    <tr>
                      <td>{{ $o->id }}</td>
                      <td><strong>{{ $o->order_code }}</strong></td>
                      <td>{{ optional($o->created_at)->format('d/m/Y H:i') ?: '-' }}</td>
                      <td>{{ optional($o->verified_at)->format('d/m/Y H:i') ?: '-' }}</td>
                      <td class="fw-semibold">{{ number_format((float) $o->total_amount, 0, ',', '.') }}đ</td>
                      <td>{{ $o->order_status }}</td>
                      <td><a href="{{ route('backend.orders.show', $o->id) }}" class="btn btn-info btn-sm text-white">Xem đơn</a></td>
                    </tr>
                  @empty
                    <tr><td colspan="7" class="text-center py-4">Không có dữ liệu.</td></tr>
                  @endforelse
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
@endsection
