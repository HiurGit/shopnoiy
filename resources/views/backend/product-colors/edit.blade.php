@extends('backend.layouts.app')
@section('title','Sửa màu sắc sản phẩm')
@section('content')
<div class="app-content-header"><div class="container-fluid"><h1 class="mb-0">Sửa màu sắc sản phẩm</h1></div></div>
<div class="app-content"><div class="container-fluid"><div class="card"><div class="card-body"><form method="POST" action="{{ route('backend.product-colors.update', $productColor) }}">@csrf @method('PUT') @include('backend.product-colors._form')</form></div></div></div></div>
@endsection
