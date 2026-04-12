@extends('backend.layouts.app')

@section('title', 'Thêm banner')

@section('content')
<div class="app-content-header">
  <div class="container-fluid">
    <h1 class="mb-0">Thêm banner</h1>
  </div>
</div>

<div class="app-content">
  <div class="container-fluid">
    <div class="card">
      <div class="card-body">
        <form method="POST" action="{{ route('backend.banners.store') }}" enctype="multipart/form-data">
          @csrf
          @include('backend.banners._form')
        </form>
      </div>
    </div>
  </div>
</div>
@endsection
