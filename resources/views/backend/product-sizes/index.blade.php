@extends('backend.layouts.app')

@section('title', 'Kích thước sản phẩm')

@section('content')
<div class="app-content-header"><div class="container-fluid"><h1 class="mb-0">Kích thước sản phẩm</h1></div></div>
<div class="app-content"><div class="container-fluid">
  @if (session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
  <div class="card"><div class="card-header d-flex justify-content-between align-items-center"><h3 class="card-title mb-0">Danh sách kích thước</h3><a href="{{ route('backend.product-sizes.create') }}" class="btn btn-primary btn-sm ms-auto">Thêm mới</a></div><div class="card-body"><table class="table table-striped table-hover align-middle mb-0"><thead><tr><th>ID</th><th>Tên size</th><th>Slug</th><th>Thứ tự</th><th>Trạng thái</th><th>Thao tác</th></tr></thead><tbody>@forelse($productSizes as $productSize)<tr><td>{{ $productSize->id }}</td><td>{{ $productSize->name }}</td><td>{{ $productSize->slug }}</td><td>{{ $productSize->sort_order }}</td><td>{{ $productSize->status }}</td><td><a href="{{ route('backend.product-sizes.show', $productSize) }}" class="btn btn-info btn-sm text-white">Xem</a> <a href="{{ route('backend.product-sizes.edit', $productSize) }}" class="btn btn-outline-primary btn-sm">Sửa</a> <form action="{{ route('backend.product-sizes.destroy', $productSize) }}" method="POST" class="d-inline" onsubmit="return confirm('Xóa kích thước này?')">@csrf @method('DELETE')<button type="submit" class="btn btn-outline-danger btn-sm">Xóa</button></form></td></tr>@empty<tr><td colspan="6" class="text-center">Chưa có dữ liệu.</td></tr>@endforelse</tbody></table></div></div>
</div></div>
@endsection

