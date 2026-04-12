@extends('backend.layouts.app')
@section('title','Sửa kích thước sản phẩm')
@section('content')
<div class="app-content-header"><div class="container-fluid"><h1 class="mb-0">Sửa kích thước sản phẩm</h1></div></div>
<div class="app-content"><div class="container-fluid"><div class="card"><div class="card-body"><form method="POST" action="{{ route('backend.product-sizes.update', $productSize) }}">@csrf @method('PUT') @include('backend.product-sizes._form')</form></div></div></div></div>
@endsection
