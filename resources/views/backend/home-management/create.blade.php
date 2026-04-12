@extends('backend.layouts.app')
@section('title','Thêm mục trang chủ')
@section('content')<div class="app-content-header"><div class="container-fluid"><h1 class="mb-0">Thêm mục trang chủ</h1></div></div><div class="app-content"><div class="container-fluid"><div class="card"><div class="card-body"><form method="POST" action="{{ route('backend.home-management.store') }}">@csrf @include('backend.home-management._form')</form></div></div></div></div>@endsection
