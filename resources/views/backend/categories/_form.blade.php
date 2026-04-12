@php
  $isEdit = isset($category);
  $selectedParentId = old('parent_id', $category->parent_id ?? $defaultParentId ?? '');
@endphp

<div class="row g-3">
  <div class="col-md-4">
    <label class="form-label">Tên danh mục <span class="text-danger">*</span></label>
    <input type="text" name="name" class="form-control" value="{{ old('name', $category->name ?? '') }}" required>
  </div>

  <div class="col-md-4">
    <label class="form-label">Slug (để trống sẽ tự tạo)</label>
    <input type="text" name="slug" class="form-control" value="{{ old('slug', $category->slug ?? '') }}">
  </div>

  <div class="col-md-6">
    <label class="form-label">Danh mục cha</label>
    <select name="parent_id" class="form-select">
      <option value="">-- Không có (danh mục cha) --</option>
      @foreach ($parentOptions as $option)
        <option value="{{ $option['id'] }}" {{ (string) $selectedParentId === (string) $option['id'] ? 'selected' : '' }}>
          {{ $option['path'] }}
        </option>
      @endforeach
    </select>
    <small class="text-secondary">Có thể chọn cha cấp 1 để tạo cấp 2, chọn cha cấp 2 để tạo cấp 3.</small>
  </div>

  <div class="col-md-6">
    <label class="form-label">Đối tượng</label>
    <select name="product_target_id" class="form-select">
      <option value="">-- Chọn đối tượng --</option>
      @foreach (($productTargets ?? collect()) as $productTarget)
        <option value="{{ $productTarget->id }}" {{ (string) old('product_target_id', $category->product_target_id ?? '') === (string) $productTarget->id ? 'selected' : '' }}>
          {{ $productTarget->name }}
        </option>
      @endforeach
    </select>
    <small class="text-secondary">Đối tượng sẽ áp dụng cho danh mục này và các sản phẩm nằm trong danh mục đó.</small>
  </div>

  <div class="col-md-2">
    <label class="form-label">Thứ tự</label>
    <input type="number" name="sort_order" class="form-control" value="{{ old('sort_order', $category->sort_order ?? 0) }}">
  </div>

  <div class="col-md-2">
    <label class="form-label">Trạng thái <span class="text-danger">*</span></label>
    <select name="status" class="form-select" required>
      <option value="active" {{ old('status', $category->status ?? 'active') === 'active' ? 'selected' : '' }}>Hoạt động</option>
      <option value="inactive" {{ old('status', $category->status ?? '') === 'inactive' ? 'selected' : '' }}>Ngừng hoạt động</option>
    </select>
  </div>

  <div class="col-md-6">
    <label class="form-label">Icon class</label>
    <input type="text" name="icon_class" class="form-control" value="{{ old('icon_class', $category->icon_class ?? '') }}" placeholder="bi bi-tag">
  </div>

  <div class="col-md-6">
    <label class="form-label">Hình ảnh danh mục</label>
    <input type="file" name="image" class="form-control" accept="image/*">
    <small class="text-secondary">Định dạng ảnh, tối đa 4MB.</small>
    @if (!empty($category?->image_url))
      <div class="mt-2">
        <img src="{{ asset($category->image_url) }}" alt="{{ $category->name }}" style="height: 80px; width: auto; border-radius: 6px;">
      </div>
    @endif
  </div>

  <div class="col-12">
    <label class="form-label">Mô tả</label>
    <textarea name="description" rows="3" class="form-control">{{ old('description', $category->description ?? '') }}</textarea>
  </div>
</div>

<div class="mt-3 d-flex gap-2">
  <button type="submit" class="btn btn-primary">{{ $isEdit ? 'Cập nhật' : 'Tạo mới' }}</button>
  <a href="{{ route('backend.categories') }}" class="btn btn-secondary">Quay lại</a>
</div>
