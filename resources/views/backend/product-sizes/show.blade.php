@extends('backend.layouts.app')
@section('title','Chi tiết kích thước')
@section('content')
<div class="app-content-header"><div class="container-fluid d-flex justify-content-between align-items-center"><h1 class="mb-0">Chi tiết kích thước</h1><a href="{{ route('backend.product-sizes') }}" class="btn btn-secondary btn-sm">Quay lại</a></div></div>
<div class="app-content"><div class="container-fluid"><div class="card"><div class="card-body"><table class="table table-bordered"><tbody><tr><th style="width:220px;">ID</th><td>{{ $productSize->id }}</td></tr><tr><th>Tên size</th><td>{{ $productSize->name }}</td></tr><tr><th>Slug</th><td>{{ $productSize->slug }}</td></tr><tr><th>Thứ tự</th><td>{{ $productSize->sort_order }}</td></tr><tr><th>Trạng thái</th><td>{{ $productSize->status }}</td></tr></tbody></table></div></div></div></div>
@endsection
