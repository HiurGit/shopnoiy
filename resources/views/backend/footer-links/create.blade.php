@extends('backend.layouts.app')
@section('title','Thêm link chân trang')
@section('content')<div class="app-content-header"><div class="container-fluid"><h1 class="mb-0">Thêm link chân trang</h1></div></div><div class="app-content"><div class="container-fluid"><div class="card"><div class="card-body"><form method="POST" action="{{ route('backend.footer-links.store') }}">@csrf @include('backend.footer-links._form')</form></div></div></div></div>@endsection
