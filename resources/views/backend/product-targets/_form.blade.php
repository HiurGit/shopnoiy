<div class="row g-3">
  <div class="col-md-6">
    <label class="form-label">Tên đối tượng</label>
    <input type="text" name="name" class="form-control" value="{{ old('name', $productTarget->name ?? '') }}">
  </div>
  <div class="col-md-6">
    <label class="form-label">Slug</label>
    <input type="text" name="slug" class="form-control" value="{{ old('slug', $productTarget->slug ?? '') }}">
  </div>
  <div class="col-md-6">
    <label class="form-label">Thứ tự</label>
    <input type="number" name="sort_order" class="form-control" value="{{ old('sort_order', $productTarget->sort_order ?? 0) }}">
  </div>
  <div class="col-md-6">
    <label class="form-label">Trạng thái</label>
    <select name="status" class="form-select">
      @foreach (['active' => 'active', 'hidden' => 'hidden'] as $value => $label)
        <option value="{{ $value }}" {{ old('status', $productTarget->status ?? 'active') === $value ? 'selected' : '' }}>{{ $label }}</option>
      @endforeach
    </select>
  </div>
</div>

<div class="mt-3 d-flex gap-2">
  <button type="submit" class="btn btn-primary">{{ isset($productTarget) ? 'Cập nhật' : 'Tạo mới' }}</button>
  <a href="{{ route('backend.product-targets') }}" class="btn btn-secondary">Quay lại</a>
</div>
