@extends('backend.layouts.app')

@section('title', 'Hoa don')
@section('use_datatable', true)

@section('content')
<div class="app-content-header">
  <div class="container-fluid">
    <h1 class="mb-0">Hoa don</h1>
  </div>
</div>

<div class="app-content">
  <div class="container-fluid">
    @if (session('success'))
      <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="card">
      <div class="card-header">
        <h3 class="card-title mb-0">Danh sach hoa don chuyen khoan</h3>
      </div>
      <div class="card-body">
        <div class="table-responsive">
          <table class="table table-striped table-bordered align-middle mb-0 js-datatable" data-order='[[0, "desc"]]'>
            <thead>
              <tr>
                <th>ID</th>
                <th>Ma hoa don</th>
                <th>Khach hang</th>
                <th>So dien thoai</th>
                <th>Thanh toan</th>
                <th>Trang thai hoa don</th>
                <th>Trang thai don</th>
                <th>Tong</th>
                <th>Ngay tao</th>
                <th>Thao tac</th>
              </tr>
            </thead>
            <tbody>
              @forelse ($invoices as $invoice)
                <tr>
                  <td>{{ $invoice->id }}</td>
                  <td><strong>{{ $invoice->invoice_code }}</strong></td>
                  <td>{{ $invoice->customer_name }}</td>
                  <td>{{ $invoice->customer_phone }}</td>
                  <td>{{ $invoice->payment_method_label }} / {{ $invoice->payment_status_label }}</td>
                  <td>{{ $invoice->invoice_status_label }}</td>
                  <td>
                    @if ($invoice->order_id)
                      <a href="{{ route('backend.orders.show', $invoice->order_id) }}" class="text-decoration-none">Da tao don #{{ $invoice->order_id }}</a>
                    @else
                      -
                    @endif
                  </td>
                  <td>{{ number_format((float) $invoice->total_amount, 0, ',', '.') }}d</td>
                  <td>{{ optional($invoice->created_at)->format('d/m/Y H:i') }}</td>
                  <td>
                    <a href="{{ route('backend.payment-invoices.show', $invoice) }}" class="btn btn-info btn-sm text-white">Xem</a>
                  </td>
                </tr>
              @empty
                <tr>
                  <td colspan="10" class="text-center">Chua co du lieu.</td>
                </tr>
              @endforelse
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</div>
@endsection
