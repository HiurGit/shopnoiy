@extends('backend.layouts.app')

@section('title', 'Thêm danh mục')

@section('content')
<div class="app-content-header">
  <div class="container-fluid">
    <h1 class="mb-0">Thêm danh mục</h1>
  </div>
</div>

<div class="app-content">
  <div class="container-fluid">
    <div class="card">
      <div class="card-header">
        <h3 class="card-title mb-0">
          @if ($type === 'child')
            Tạo danh mục con
          @elseif ($type === 'grandchild')
            Tạo danh mục cháu
          @else
            Tạo danh mục cha
          @endif
        </h3>
      </div>
      <div class="card-body">
        <form method="POST" action="{{ route('backend.categories.store') }}" enctype="multipart/form-data">
          @csrf
          @include('backend.categories._form')
        </form>
      </div>
    </div>
  </div>
</div>
@endsection
