@extends('backend.layouts.app')

@section('title', 'Thêm sản phẩm')

@section('content')
<div class="app-content-header"><div class="container-fluid"><h1 class="mb-0">Thêm sản phẩm</h1></div></div>
<div class="app-content">
  <div class="container-fluid">
    <div class="card">
      <div class="card-header"><h3 class="card-title mb-0">Tạo sản phẩm mới</h3></div>
      <div class="card-body">
        <form method="POST" action="{{ route('backend.products.store') }}" enctype="multipart/form-data">
          @csrf
          @include('backend.products._form')
        </form>
      </div>
    </div>
  </div>
</div>
@endsection
