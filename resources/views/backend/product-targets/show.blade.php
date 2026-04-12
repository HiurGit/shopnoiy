@extends('backend.layouts.app')
@section('title','Chi tiết đối tượng sản phẩm')
@section('content')
<div class="app-content-header"><div class="container-fluid d-flex justify-content-between align-items-center"><h1 class="mb-0">Chi tiết đối tượng danh mục</h1><a href="{{ route('backend.product-targets') }}" class="btn btn-secondary btn-sm">Quay lại</a></div></div>
<div class="app-content"><div class="container-fluid"><div class="card"><div class="card-body"><table class="table table-bordered"><tbody><tr><th style="width:220px;">ID</th><td>{{ $productTarget->id }}</td></tr><tr><th>Tên</th><td>{{ $productTarget->name }}</td></tr><tr><th>Slug</th><td>{{ $productTarget->slug }}</td></tr><tr><th>Thứ tự</th><td>{{ $productTarget->sort_order }}</td></tr><tr><th>Trạng thái</th><td>{{ $productTarget->status }}</td></tr></tbody></table></div></div></div></div>
@endsection
