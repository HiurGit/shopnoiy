@extends('backend.layouts.app')
@section('title','Thêm khuyến mãi')
@section('content')
<div class="app-content-header"><div class="container-fluid"><h1 class="mb-0">Thêm khuyến mãi</h1></div></div>
<div class="app-content"><div class="container-fluid"><div class="card"><div class="card-body"><form method="POST" action="{{ route('backend.promotions.store') }}">@csrf @include('backend.promotions._form')</form></div></div></div></div>
@endsection
