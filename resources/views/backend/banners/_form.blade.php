@php
  $currentImage = old('image_url', $banner->image_url ?? '');
  $imagePreviewUrl = $currentImage
    ? (str_starts_with($currentImage, 'http://') || str_starts_with($currentImage, 'https://') ? $currentImage : asset($currentImage))
    : null;
@endphp

<div class="row g-3">
  <div class="col-md-6">
    <label class="form-label">Vi tri hien thi</label>
    <select name="section_id" class="form-select">
      @foreach ($positions as $position)
        <option value="{{ $position->id }}" {{ (string) old('section_id', $banner->section_id ?? '') === (string) $position->id ? 'selected' : '' }}>
          {{ $position->title }} ({{ $position->section_key }})
        </option>
      @endforeach
    </select>
  </div>

  <div class="col-md-3">
    <label class="form-label">Thu tu</label>
    <input type="number" name="sort_order" class="form-control" value="{{ old('sort_order', $banner->sort_order ?? 0) }}">
  </div>

  <div class="col-md-3 d-flex align-items-end">
    <div class="form-check">
      <input class="form-check-input" type="checkbox" name="is_active" value="1" {{ old('is_active', $banner->is_active ?? true) ? 'checked' : '' }}>
      <label class="form-check-label">Kich hoat</label>
    </div>
  </div>

  <div class="col-md-6">
    <label class="form-label">Tieu de</label>
    <input type="text" name="title" class="form-control" value="{{ old('title', $banner->title ?? '') }}">
  </div>

  <div class="col-md-6">
    <label class="form-label">Phu de</label>
    <input type="text" name="subtitle" class="form-control" value="{{ old('subtitle', $banner->subtitle ?? '') }}">
  </div>

  <div class="col-md-6">
    <label class="form-label">Upload hinh banner</label>
    <input type="file" name="image" class="form-control" accept="image/*">
    <small class="text-secondary">Neu chon file, anh se duoc luu vao `public/uploads/banners`.</small>
    @if ($imagePreviewUrl)
      <div class="mt-2">
        <img src="{{ $imagePreviewUrl }}" alt="{{ $banner->title ?: 'Banner' }}" style="height: 100px; width: auto; border-radius: 6px; object-fit: cover;">
      </div>
    @endif
  </div>

  <div class="col-md-6">
    <label class="form-label">Anh banner (URL hoac duong dan)</label>
    <input type="text" name="image_url" class="form-control" value="{{ old('image_url', $banner->image_url ?? '') }}" placeholder="/uploads/banners/banner.jpg">
    <small class="text-secondary">Ban van co the nhap URL neu khong muon upload file.</small>
  </div>

  <div class="col-md-6">
    <label class="form-label">Link dich</label>
    <input type="text" name="target_url" class="form-control" value="{{ old('target_url', $banner->target_url ?? '') }}">
  </div>

  <div class="col-md-4">
    <label class="form-label">Ref ID</label>
    <input type="number" name="ref_id" class="form-control" value="{{ old('ref_id', $banner->ref_id ?? '') }}">
  </div>

  <div class="col-md-4">
    <label class="form-label">Bat dau</label>
    <input type="datetime-local" name="start_at" class="form-control" value="{{ old('start_at', isset($banner) && $banner->start_at ? \Carbon\Carbon::parse($banner->start_at)->format('Y-m-d\TH:i') : '') }}">
  </div>

  <div class="col-md-4">
    <label class="form-label">Ket thuc</label>
    <input type="datetime-local" name="end_at" class="form-control" value="{{ old('end_at', isset($banner) && $banner->end_at ? \Carbon\Carbon::parse($banner->end_at)->format('Y-m-d\TH:i') : '') }}">
  </div>

  <div class="col-12">
    <label class="form-label">Meta JSON</label>
    <textarea name="meta_json" class="form-control" rows="3">{{ old('meta_json', $banner->meta_json ?? '') }}</textarea>
  </div>
</div>

<div class="mt-3 d-flex gap-2">
  <button class="btn btn-primary" type="submit">{{ isset($banner) ? 'Cap nhat' : 'Tao moi' }}</button>
  <a href="{{ route('backend.banners') }}" class="btn btn-secondary">Quay lai</a>
</div>
