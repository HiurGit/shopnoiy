<div class="row g-3">
  <div class="col-md-6"><label class="form-label">Tên màu</label><input type="text" name="name" class="form-control" value="{{ old('name', $productColor->name ?? '') }}"></div>
  <div class="col-md-6"><label class="form-label">Slug</label><input type="text" name="slug" class="form-control" value="{{ old('slug', $productColor->slug ?? '') }}"></div>
  <div class="col-md-4"><label class="form-label">Mã HEX</label><input type="text" name="hex_code" class="form-control" placeholder="#000000" value="{{ old('hex_code', $productColor->hex_code ?? '') }}"></div>
  <div class="col-md-4"><label class="form-label">Thứ tự</label><input type="number" name="sort_order" class="form-control" value="{{ old('sort_order', $productColor->sort_order ?? 0) }}"></div>
  <div class="col-md-4"><label class="form-label">Trạng thái</label><input type="text" name="status" class="form-control" value="{{ old('status', $productColor->status ?? 'active') }}"></div>
</div>
<div class="mt-3 d-flex gap-2"><button type="submit" class="btn btn-primary">{{ isset($productColor) ? 'Cập nhật' : 'Tạo mới' }}</button><a href="{{ route('backend.product-colors') }}" class="btn btn-secondary">Quay lại</a></div>
