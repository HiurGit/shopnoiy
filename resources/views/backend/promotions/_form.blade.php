<div class="row g-3">
  <div class="col-md-6"><label class="form-label">Tên</label><input type="text" name="name" class="form-control" value="{{ old('name', $promotion->name ?? '') }}"></div>
  <div class="col-md-6"><label class="form-label">Mã</label><input type="text" name="code" class="form-control" value="{{ old('code', $promotion->code ?? '') }}"></div>
  <div class="col-md-4"><label class="form-label">Loại</label><input type="text" name="promotion_type" class="form-control" value="{{ old('promotion_type', $promotion->promotion_type ?? 'voucher') }}"></div>
  <div class="col-md-4"><label class="form-label">Kênh</label><input type="text" name="channel" class="form-control" value="{{ old('channel', $promotion->channel ?? 'all') }}"></div>
  <div class="col-md-4"><label class="form-label">Trạng thái</label><input type="text" name="status" class="form-control" value="{{ old('status', $promotion->status ?? 'active') }}"></div>
  <div class="col-md-4"><label class="form-label">Kiểu giảm</label><input type="text" name="discount_type" class="form-control" value="{{ old('discount_type', $promotion->discount_type ?? 'none') }}"></div>
  <div class="col-md-4"><label class="form-label">Giá trị giảm</label><input type="number" step="0.01" name="discount_value" class="form-control" value="{{ old('discount_value', $promotion->discount_value ?? 0) }}"></div>
  <div class="col-md-4"><label class="form-label">Min đơn</label><input type="number" step="0.01" name="min_order_value" class="form-control" value="{{ old('min_order_value', $promotion->min_order_value ?? 0) }}"></div>
  <div class="col-md-6"><label class="form-label">Bắt đầu</label><input type="datetime-local" name="start_at" class="form-control" value="{{ old('start_at', isset($promotion) && $promotion->start_at ? \Carbon\Carbon::parse($promotion->start_at)->format('Y-m-d\TH:i') : '') }}"></div>
  <div class="col-md-6"><label class="form-label">Kết thúc</label><input type="datetime-local" name="end_at" class="form-control" value="{{ old('end_at', isset($promotion) && $promotion->end_at ? \Carbon\Carbon::parse($promotion->end_at)->format('Y-m-d\TH:i') : '') }}"></div>
  <div class="col-12"><label class="form-label">Mô tả</label><textarea name="description" class="form-control" rows="3">{{ old('description', $promotion->description ?? '') }}</textarea></div>
</div>
<div class="mt-3 d-flex gap-2"><button type="submit" class="btn btn-primary">{{ isset($promotion) ? 'Cập nhật' : 'Tạo mới' }}</button><a href="{{ route('backend.promotions') }}" class="btn btn-secondary">Quay lại</a></div>
