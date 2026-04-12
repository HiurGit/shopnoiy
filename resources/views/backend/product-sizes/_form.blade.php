<div class="row g-3">
  <div class="col-md-6"><label class="form-label">Tên size</label><input type="text" name="name" class="form-control" value="{{ old('name', $productSize->name ?? '') }}"></div>
  <div class="col-md-6"><label class="form-label">Slug</label><input type="text" name="slug" class="form-control" value="{{ old('slug', $productSize->slug ?? '') }}"></div>
  <div class="col-md-6"><label class="form-label">Thứ tự</label><input type="number" name="sort_order" class="form-control" value="{{ old('sort_order', $productSize->sort_order ?? 0) }}"></div>
  <div class="col-md-6"><label class="form-label">Trạng thái</label><input type="text" name="status" class="form-control" value="{{ old('status', $productSize->status ?? 'active') }}"></div>
</div>
<div class="mt-3 d-flex gap-2"><button type="submit" class="btn btn-primary">{{ isset($productSize) ? 'Cập nhật' : 'Tạo mới' }}</button><a href="{{ route('backend.product-sizes') }}" class="btn btn-secondary">Quay lại</a></div>
