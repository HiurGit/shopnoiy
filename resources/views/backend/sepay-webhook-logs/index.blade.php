@extends('backend.layouts.app')

@section('title', 'Nhat ky SePay')

@section('content')
<div class="app-content-header">
  <div class="container-fluid">
    <h1 class="mb-0">Nhat ky SePay</h1>
  </div>
</div>

<div class="app-content">
  <div class="container-fluid">
    <div class="card">
      <div class="card-header">
        <h3 class="card-title mb-0">Lich su webhook SePay</h3>
      </div>
      <div class="card-body">
        <div class="table-responsive">
          <table class="table table-striped table-bordered align-middle mb-0">
            <thead>
              <tr>
                <th>ID</th>
                <th>Thoi gian</th>
                <th>Trang thai</th>
                <th>HTTP</th>
                <th>Giao dich</th>
                <th>Hoa don</th>
                <th>Don hang</th>
                <th>Auth</th>
                <th>Thong diep</th>
                <th>Payload</th>
              </tr>
            </thead>
            <tbody>
              @forelse ($logs as $log)
                <tr>
                  <td>{{ $log->id }}</td>
                  <td>{{ \Carbon\Carbon::parse($log->created_at)->format('d/m/Y H:i:s') }}</td>
                  <td><strong>{{ $log->status }}</strong></td>
                  <td>{{ $log->http_status }}</td>
                  <td>{{ $log->transaction_id ?: '-' }}</td>
                  <td>
                    @if ($log->invoice_id)
                      <a href="{{ route('backend.payment-invoices.show', $log->invoice_id) }}" class="text-decoration-none">#{{ $log->invoice_id }}</a>
                    @else
                      -
                    @endif
                  </td>
                  <td>
                    @if ($log->order_id)
                      <a href="{{ route('backend.orders.show', $log->order_id) }}" class="text-decoration-none">#{{ $log->order_id }}</a>
                    @else
                      -
                    @endif
                  </td>
                  <td>
                    <div>{{ $log->auth_type ?: '-' }}</div>
                    @if ($log->secret_preview)
                      <div class="small text-muted">{{ $log->secret_preview }}</div>
                    @endif
                  </td>
                  <td>{{ $log->message ?: '-' }}</td>
                  <td style="min-width: 320px;">
                    <details>
                      <summary>Xem</summary>
                      <pre class="small mb-0 mt-2" style="white-space: pre-wrap;">{{ json_encode(json_decode($log->payload_json ?? 'null', true), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) }}</pre>
                    </details>
                  </td>
                </tr>
              @empty
                <tr>
                  <td colspan="10" class="text-center">Chua co ban ghi nao.</td>
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
