<div class="row g-3">
<div class="col-md-4"><label class="form-label">Mã</label><input type="text" name="code" class="form-control" value="{{ old('code', $store->code ?? '') }}"></div>
<div class="col-md-8"><label class="form-label">Tên</label><input type="text" name="name" class="form-control" value="{{ old('name', $store->name ?? '') }}"></div>
<div class="col-md-4"><label class="form-label">Phone</label><input type="text" name="phone" class="form-control" value="{{ old('phone', $store->phone ?? '') }}"></div>
<div class="col-md-4"><label class="form-label">Email</label><input type="text" name="email" class="form-control" value="{{ old('email', $store->email ?? '') }}"></div>
<div class="col-md-4"><label class="form-label">Status</label><input type="text" name="status" class="form-control" value="{{ old('status', $store->status ?? 'active') }}"></div>
<div class="col-md-4"><label class="form-label">Province</label><input type="text" name="province" class="form-control" value="{{ old('province', $store->province ?? '') }}"></div>
<div class="col-md-4"><label class="form-label">District</label><input type="text" name="district" class="form-control" value="{{ old('district', $store->district ?? '') }}"></div>
<div class="col-md-4"><label class="form-label">Ward</label><input type="text" name="ward" class="form-control" value="{{ old('ward', $store->ward ?? '') }}"></div>
<div class="col-md-8"><label class="form-label">Address</label><input type="text" name="address_line" class="form-control" value="{{ old('address_line', $store->address_line ?? '') }}"></div>
<div class="col-md-2"><label class="form-label">Open</label><input type="time" name="open_time" class="form-control" value="{{ old('open_time', $store->open_time ?? '') }}"></div>
<div class="col-md-2"><label class="form-label">Close</label><input type="time" name="close_time" class="form-control" value="{{ old('close_time', $store->close_time ?? '') }}"></div>
<div class="col-md-2"><label class="form-label">Priority</label><input type="number" name="priority_order" class="form-control" value="{{ old('priority_order', $store->priority_order ?? 0) }}"></div>
<div class="col-md-2 d-flex align-items-end"><div class="form-check"><input class="form-check-input" type="checkbox" name="pickup_enabled" value="1" {{ old('pickup_enabled', $store->pickup_enabled ?? true) ? 'checked' : '' }}><label class="form-check-label">Pickup</label></div></div>
</div><div class="mt-3 d-flex gap-2"><button class="btn btn-primary" type="submit">{{ isset($store) ? 'Cập nhật' : 'Tạo mới' }}</button><a href="{{ route('backend.stores') }}" class="btn btn-secondary">Quay lại</a></div>
