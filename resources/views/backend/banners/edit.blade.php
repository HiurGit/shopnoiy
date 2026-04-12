@extends('backend.layouts.app')

@section('title', 'Sửa banner')

@section('content')
<div class="app-content-header">
  <div class="container-fluid">
    <h1 class="mb-0">Sửa banner</h1>
  </div>
</div>

<div class="app-content">
  <div class="container-fluid">
    <div class="card">
      <div class="card-body">
        <form method="POST" action="{{ route('backend.banners.update', $banner) }}" enctype="multipart/form-data">
          @csrf
          @method('PUT')
          @include('backend.banners._form')
        </form>
      </div>
    </div>
  </div>
</div>
@endsection
