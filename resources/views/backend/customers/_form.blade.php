<div class="row g-3">
<div class="col-md-6"><label class="form-label">Họ tên</label><input type="text" name="full_name" class="form-control" value="{{ old('full_name', $customer->full_name ?? '') }}"></div>
<div class="col-md-6"><label class="form-label">Email</label><input type="email" name="email" class="form-control" value="{{ old('email', $customer->email ?? '') }}"></div>
<div class="col-md-4"><label class="form-label">Điện thoại</label><input type="text" name="phone" class="form-control" value="{{ old('phone', $customer->phone ?? '') }}"></div>
<div class="col-md-4"><label class="form-label">Mật khẩu</label><input type="text" name="password" class="form-control" placeholder="Bỏ trống để giữ nguyên"></div>
<div class="col-md-4"><label class="form-label">Trạng thái</label><input type="text" name="status" class="form-control" value="{{ old('status', $customer->status ?? 'active') }}"></div>
<div class="col-md-4"><label class="form-label">Rank</label><input type="text" name="tier" class="form-control" value="{{ old('tier', $profile->tier ?? 'new') }}"><div class="form-text">Rank có thể được cập nhật lại tự động theo tổng mua trong Cấu hình rank. Giá trị đang dùng: new, friendly, loyal, vip, diamond.</div></div>
<div class="col-md-4"><label class="form-label">Tổng đơn</label><input type="number" name="total_orders" class="form-control" value="{{ old('total_orders', $profile->total_orders ?? 0) }}"></div>
<div class="col-md-4"><label class="form-label">Tổng chi</label><input type="number" step="0.01" name="total_spent" class="form-control" value="{{ old('total_spent', $profile->total_spent ?? 0) }}"></div>
</div><div class="mt-3 d-flex gap-2"><button class="btn btn-primary" type="submit">{{ isset($customer) ? 'Cập nhật' : 'Tạo mới' }}</button><a href="{{ route('backend.customers') }}" class="btn btn-secondary">Quay lại</a></div>
