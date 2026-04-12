<div class="row g-3">
<div class="col-md-4"><label class="form-label">Nhóm</label><input type="text" name="group_name" class="form-control" value="{{ old('group_name', $footerLink->group_name ?? '') }}"></div>
<div class="col-md-4"><label class="form-label">Tiêu đề</label><input type="text" name="title" class="form-control" value="{{ old('title', $footerLink->title ?? '') }}"></div>
<div class="col-md-4"><label class="form-label">Sort</label><input type="number" name="sort_order" class="form-control" value="{{ old('sort_order', $footerLink->sort_order ?? 0) }}"></div>
<div class="col-md-12"><label class="form-label">URL</label><input type="text" name="url" class="form-control" value="{{ old('url', $footerLink->url ?? '') }}"></div>
<div class="col-md-3 d-flex align-items-end"><div class="form-check"><input class="form-check-input" type="checkbox" name="is_active" value="1" {{ old('is_active', $footerLink->is_active ?? true) ? 'checked' : '' }}><label class="form-check-label">Hoạt động</label></div></div>
</div><div class="mt-3 d-flex gap-2"><button class="btn btn-primary" type="submit">{{ isset($footerLink) ? 'Cập nhật' : 'Tạo mới' }}</button><a href="{{ route('backend.footer-links') }}" class="btn btn-secondary">Quay lại</a></div>
