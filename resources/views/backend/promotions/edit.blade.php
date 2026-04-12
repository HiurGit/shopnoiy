@extends('backend.layouts.app')
@section('title','Sửa khuyến mãi')
@section('content')
<div class="app-content-header"><div class="container-fluid"><h1 class="mb-0">Sửa khuyến mãi</h1></div></div>
<div class="app-content"><div class="container-fluid"><div class="card"><div class="card-body"><form method="POST" action="{{ route('backend.promotions.update', $promotion) }}">@csrf @method('PUT') @include('backend.promotions._form')</form></div></div></div></div>
@endsection
