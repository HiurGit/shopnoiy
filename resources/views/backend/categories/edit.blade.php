@extends('backend.layouts.app')

@section('title', 'Sửa danh mục')

@section('content')
<div class="app-content-header">
  <div class="container-fluid">
    <h1 class="mb-0">Sửa danh mục</h1>
  </div>
</div>

<div class="app-content">
  <div class="container-fluid">
    <div class="card">
      <div class="card-header">
        <h3 class="card-title mb-0">Chỉnh sửa: {{ $category->name }}</h3>
      </div>
      <div class="card-body">
        <form method="POST" action="{{ route('backend.categories.update', $category) }}" enctype="multipart/form-data">
          @csrf
          @method('PUT')
          @include('backend.categories._form')
        </form>
      </div>
    </div>
  </div>
</div>
@endsection
