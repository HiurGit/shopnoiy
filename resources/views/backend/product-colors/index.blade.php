@extends('backend.layouts.app')

@section('title', 'Màu sắc sản phẩm')

@section('content')
<div class="app-content-header"><div class="container-fluid"><h1 class="mb-0">Màu sắc sản phẩm</h1></div></div>
<div class="app-content"><div class="container-fluid">
  @if (session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
  <div class="card"><div class="card-header d-flex justify-content-between align-items-center"><h3 class="card-title mb-0">Danh sách màu sắc</h3><a href="{{ route('backend.product-colors.create') }}" class="btn btn-primary btn-sm ms-auto">Thêm mới</a></div><div class="card-body"><table class="table table-striped table-hover align-middle mb-0"><thead><tr><th>ID</th><th>Tên màu</th><th>Slug</th><th>Mã HEX</th><th>Thứ tự</th><th>Trạng thái</th><th>Thao tác</th></tr></thead><tbody>@forelse($productColors as $productColor)<tr><td>{{ $productColor->id }}</td><td>{{ $productColor->name }}</td><td>{{ $productColor->slug }}</td><td>@if($productColor->hex_code)<span class="d-inline-block rounded border me-1 align-middle" style="width:14px;height:14px;background:{{ $productColor->hex_code }};"></span>{{ $productColor->hex_code }}@endif</td><td>{{ $productColor->sort_order }}</td><td>{{ $productColor->status }}</td><td><a href="{{ route('backend.product-colors.show', $productColor) }}" class="btn btn-info btn-sm text-white">Xem</a> <a href="{{ route('backend.product-colors.edit', $productColor) }}" class="btn btn-outline-primary btn-sm">Sửa</a> <form action="{{ route('backend.product-colors.destroy', $productColor) }}" method="POST" class="d-inline" onsubmit="return confirm('Xóa màu sắc này?')">@csrf @method('DELETE')<button type="submit" class="btn btn-outline-danger btn-sm">Xóa</button></form></td></tr>@empty<tr><td colspan="7" class="text-center">Chưa có dữ liệu.</td></tr>@endforelse</tbody></table></div></div>
</div></div>
@endsection

