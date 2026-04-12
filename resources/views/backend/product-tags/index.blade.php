@extends('backend.layouts.app')

@section('title', 'Tag sản phẩm')

@section('content')
<div class="app-content-header"><div class="container-fluid"><h1 class="mb-0">Tag sản phẩm</h1></div></div>
<div class="app-content"><div class="container-fluid">
  @if (session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
  <div class="card"><div class="card-header d-flex justify-content-between align-items-center"><h3 class="card-title mb-0">Danh sách tag</h3><a href="{{ route('backend.product-tags.create') }}" class="btn btn-primary btn-sm ms-auto">Thêm mới</a></div><div class="card-body"><table class="table table-striped table-hover align-middle mb-0"><thead><tr><th>ID</th><th>Tên tag</th><th>Slug</th><th>Thứ tự</th><th>Trạng thái</th><th>Thao tác</th></tr></thead><tbody>@forelse($productTags as $productTag)<tr><td>{{ $productTag->id }}</td><td>{{ $productTag->name }}</td><td>{{ $productTag->slug }}</td><td>{{ $productTag->sort_order }}</td><td>{{ $productTag->status }}</td><td><a href="{{ route('backend.product-tags.show', $productTag) }}" class="btn btn-info btn-sm text-white">Xem</a> <a href="{{ route('backend.product-tags.edit', $productTag) }}" class="btn btn-outline-primary btn-sm">Sửa</a> <form action="{{ route('backend.product-tags.destroy', $productTag) }}" method="POST" class="d-inline" onsubmit="return confirm('Xóa tag sản phẩm này?')">@csrf @method('DELETE')<button type="submit" class="btn btn-outline-danger btn-sm">Xóa</button></form></td></tr>@empty<tr><td colspan="6" class="text-center">Chưa có dữ liệu.</td></tr>@endforelse</tbody></table></div></div>
</div></div>
@endsection
