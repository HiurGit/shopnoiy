@extends('backend.layouts.app')
@section('title','Khách hàng')
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
<div class="app-content-header"><div class="container-fluid d-flex justify-content-between align-items-center flex-wrap gap-2"><h1 class="mb-0">Khách hàng</h1><div class="d-flex gap-2"><a href="{{ route('backend.customers.ranking') }}" class="btn btn-outline-warning btn-sm"><i class="bi bi-trophy me-1"></i>Xếp hạng mua hàng</a><a href="{{ route('backend.customers.create') }}" class="btn btn-primary btn-sm">Thêm mới</a></div></div></div>
<div class="app-content"><div class="container-fluid">@if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif<div class="card"><div class="card-header d-flex justify-content-between align-items-center"><h3 class="card-title mb-0">Danh sách khách hàng</h3></div><div class="card-body"><div class="table-responsive"><table class="table table-striped table-hover align-middle mb-0"><thead><tr><th>ID</th><th>Họ tên</th><th>Email</th><th>Điện thoại</th><th>Rank</th><th>Trạng thái</th><th>Thao tác</th></tr></thead><tbody>@forelse($customers as $customer)<tr><td>{{ $customer->id }}</td><td>{{ $customer->full_name }}</td><td>{{ $customer->email }}</td><td>{{ $customer->phone }}</td><td>{{ $tierLabels[$customer->tier ?? 'new'] ?? ($customer->tier ?? 'Khách hàng mới') }}</td><td>{{ $customer->status }}</td><td><a href="{{ route('backend.customers.show', $customer->id) }}" class="btn btn-info btn-sm text-white">Xem</a> <a href="{{ route('backend.customers.edit', $customer->id) }}" class="btn btn-outline-primary btn-sm">Sửa</a> <form action="{{ route('backend.customers.destroy', $customer->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Xóa khách hàng này?')">@csrf @method('DELETE')<button class="btn btn-outline-danger btn-sm" type="submit">Xóa</button></form></td></tr>@empty<tr><td colspan="7" class="text-center">Chưa có dữ liệu.</td></tr>@endforelse</tbody></table></div><div class="mt-3">{{ $customers->links() }}</div></div></div></div></div>
@endsection
