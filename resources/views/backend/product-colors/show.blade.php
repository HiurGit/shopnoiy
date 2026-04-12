@extends('backend.layouts.app')
@section('title','Chi tiết màu sắc')
@section('content')
<div class="app-content-header"><div class="container-fluid d-flex justify-content-between align-items-center"><h1 class="mb-0">Chi tiết màu sắc</h1><a href="{{ route('backend.product-colors') }}" class="btn btn-secondary btn-sm">Quay lại</a></div></div>
<div class="app-content"><div class="container-fluid"><div class="card"><div class="card-body"><table class="table table-bordered"><tbody><tr><th style="width:220px;">ID</th><td>{{ $productColor->id }}</td></tr><tr><th>Tên màu</th><td>{{ $productColor->name }}</td></tr><tr><th>Slug</th><td>{{ $productColor->slug }}</td></tr><tr><th>Mã HEX</th><td>@if($productColor->hex_code)<span class="d-inline-block rounded border me-1 align-middle" style="width:14px;height:14px;background:{{ $productColor->hex_code }};"></span>{{ $productColor->hex_code }}@endif</td></tr><tr><th>Thứ tự</th><td>{{ $productColor->sort_order }}</td></tr><tr><th>Trạng thái</th><td>{{ $productColor->status }}</td></tr></tbody></table></div></div></div></div>
@endsection
