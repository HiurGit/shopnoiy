@extends('backend.layouts.app')

@section('title', 'Đối tượng danh mục')

@section('content')
<div class="app-content-header"><div class="container-fluid"><h1 class="mb-0">Đối tượng danh mục</h1></div></div>
<div class="app-content"><div class="container-fluid">
  @if (session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
  <div class="card"><div class="card-header d-flex justify-content-between align-items-center"><h3 class="card-title mb-0">Danh sách đối tượng</h3><a href="{{ route('backend.product-targets.create') }}" class="btn btn-primary btn-sm ms-auto">Thêm mới</a></div><div class="card-body"><table class="table table-striped table-hover align-middle mb-0"><thead><tr><th>ID</th><th>Tên</th><th>Slug</th><th>Thứ tự</th><th>Trạng thái</th><th>Thao tác</th></tr></thead><tbody>@forelse($productTargets as $productTarget)<tr><td>{{ $productTarget->id }}</td><td>{{ $productTarget->name }}</td><td>{{ $productTarget->slug }}</td><td>{{ $productTarget->sort_order }}</td><td>{{ $productTarget->status }}</td><td><a href="{{ route('backend.product-targets.show', $productTarget) }}" class="btn btn-info btn-sm text-white">Xem</a> <a href="{{ route('backend.product-targets.edit', $productTarget) }}" class="btn btn-outline-primary btn-sm">Sửa</a> <form action="{{ route('backend.product-targets.destroy', $productTarget) }}" method="POST" class="d-inline" onsubmit="return confirm('Xóa đối tượng danh mục này?')">@csrf @method('DELETE')<button type="submit" class="btn btn-outline-danger btn-sm">Xóa</button></form></td></tr>@empty<tr><td colspan="6" class="text-center">Chưa có dữ liệu.</td></tr>@endforelse</tbody></table></div></div>
</div></div>
@endsection
