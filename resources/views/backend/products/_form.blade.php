@php
  $selectedColorIds = old('color_ids', isset($product) ? $product->colors->pluck('id')->all() : []);
  $selectedSizeIds = old('size_ids', isset($product) ? $product->sizes->pluck('id')->all() : []);
  $selectedTagIds = old('tag_ids', isset($product) ? $product->tags->pluck('id')->all() : []);
  $selectedColorImageMap = old('color_image_map', $colorImageMap ?? []);
  $priceValue = old('price', isset($product) ? (string) $product->price : '0');

  if (is_numeric($priceValue)) {
    $priceValue = rtrim(rtrim((string) $priceValue, '0'), '.');
    $priceValue = $priceValue === '' ? '0' : $priceValue;
  }
@endphp

@if ($errors->any())
  <div class="alert alert-danger">
    <div class="fw-semibold mb-2">Không thể lưu sản phẩm. Vui lòng kiểm tra lại:</div>
    <ul class="mb-0 ps-3">
      @foreach ($errors->all() as $error)
        <li>{{ $error }}</li>
      @endforeach
    </ul>
  </div>
@endif

<div class="row g-3">
  <div class="col-md-6">
    <label class="form-label">Tên sản phẩm</label>
    <input type="text" name="name" class="form-control" value="{{ old('name', $product->name ?? '') }}">
    @error('name')
      <div class="text-danger small mt-1">{{ $message }}</div>
    @enderror
  </div>
  <div class="col-md-6">
    <label class="form-label">Slug</label>
    <input type="text" name="slug" class="form-control" value="{{ old('slug', $product->slug ?? '') }}">
    @error('slug')
      <div class="text-danger small mt-1">{{ $message }}</div>
    @enderror
  </div>
  <div class="col-md-6">
    <label class="form-label">Danh mục</label>
    <select name="category_id" class="form-select">
      <option value="">-- Chọn danh mục --</option>
      @foreach ($categories as $category)
        <option value="{{ $category->id }}" {{ (string) old('category_id', $product->category_id ?? '') === (string) $category->id ? 'selected' : '' }}>{{ $category->display_name ?? $category->name }}</option>
      @endforeach
    </select>
    <small class="text-secondary">Đối tượng của sản phẩm sẽ lấy theo danh mục đã chọn.</small>
  </div>
    <div class="col-md-6">
    <label class="form-label">Giá</label>
    <input type="number" step="1" min="0" name="price" class="form-control" value="{{ $priceValue }}">
    @error('price')
      <div class="text-danger small mt-1">{{ $message }}</div>
    @enderror
  </div>
  <div class="col-md-3">
    <label class="form-label">Màu sắc (chọn nhiều)</label>
    <select name="color_ids[]" class="form-select js-multi-select" multiple data-placeholder="Chọn màu sắc">
      @foreach ($colors as $color)
        <option value="{{ $color->id }}" {{ in_array($color->id, $selectedColorIds) ? 'selected' : '' }}>{{ $color->name }}</option>
      @endforeach
    </select>
  </div>
  <div class="col-md-3">
    <label class="form-label">Kích thước (chọn nhiều)</label>
    <select name="size_ids[]" class="form-select js-multi-select" multiple data-placeholder="Chọn kích thước">
      @foreach ($sizes as $size)
        <option value="{{ $size->id }}" {{ in_array($size->id, $selectedSizeIds) ? 'selected' : '' }}>{{ $size->name }}</option>
      @endforeach
    </select>
  </div>
  <div class="col-md-12">
    <label class="form-label">Anh dai dien theo mau (dung anh san pham ben duoi)</label>
    <div class="small text-secondary mb-2">Chon mau o tren truoc, sau do gan anh tuong ung cho tung mau. Chi nhan anh da co trong san pham nay.</div>
    <div class="row g-2 js-color-image-map-wrap">
      @foreach ($colors as $color)
        @php
          $isSelectedColor = in_array($color->id, $selectedColorIds);
          $selectedImageUrl = trim((string) ($selectedColorImageMap[$color->id] ?? ''));
          $selectedPreviewUrl = $selectedImageUrl !== ''
            ? ((str_starts_with($selectedImageUrl, 'http://') || str_starts_with($selectedImageUrl, 'https://'))
                ? $selectedImageUrl
                : asset(ltrim($selectedImageUrl, '/')))
            : '';
        @endphp
        <div class="col-md-4 js-color-image-map-item{{ $isSelectedColor ? '' : ' d-none' }}" data-color-id="{{ $color->id }}">
          <div class="border rounded p-2 h-100 bg-light-subtle">
            <div class="fw-semibold small mb-2">{{ $color->name }}</div>
            <select name="color_image_map[{{ $color->id }}]" class="form-select form-select-sm js-color-image-select" data-color-id="{{ $color->id }}">
              <option value="">-- Khong gan anh rieng --</option>
              @foreach ($productImages as $img)
                <option value="{{ $img->image_url }}" {{ $selectedImageUrl !== '' && $selectedImageUrl === (string) $img->image_url ? 'selected' : '' }}>
                  Anh #{{ $loop->iteration }}{{ $img->is_primary ? ' (anh chinh)' : '' }}
                </option>
              @endforeach
            </select>
            <div class="mt-2 ratio ratio-1x1 bg-white rounded overflow-hidden border js-color-image-preview{{ $selectedPreviewUrl === '' ? ' d-none' : '' }}" data-color-preview="{{ $color->id }}">
              <img src="{{ $selectedPreviewUrl }}" alt="{{ $color->name }}" class="w-100 h-100 object-fit-cover">
            </div>
          </div>
        </div>
      @endforeach
    </div>
  </div>
  <div class="col-md-6">
    <label class="form-label">Tag sản phẩm (chọn nhiều)</label>
    <div class="tag-picker js-tag-picker">
      <div class="tag-picker-toolbar">
        <input
          type="text"
          class="form-control tag-picker-search js-tag-picker-search"
          placeholder="Tìm nhanh tag sản phẩm..."
          autocomplete="off"
        >
        <div class="tag-picker-hint">Có thể chọn nhiều tag cho một sản phẩm.</div>
      </div>
      <div class="tag-picker-selected js-tag-picker-selected"></div>
      <div class="tag-picker-list js-tag-picker-list">
        @foreach ($tags as $tag)
          <label
            class="tag-picker-item js-tag-picker-item {{ in_array($tag->id, $selectedTagIds) ? 'is-selected' : '' }}"
            data-tag-id="{{ $tag->id }}"
            data-tag-search="{{ \Illuminate\Support\Str::lower($tag->name) }}"
          >
            <input
              type="checkbox"
              name="tag_ids[]"
              value="{{ $tag->id }}"
              class="form-check-input js-tag-picker-checkbox"
              data-tag-id="{{ $tag->id }}"
              data-tag-label="{{ $tag->name }}"
              {{ in_array($tag->id, $selectedTagIds) ? 'checked' : '' }}
            >
            <span>{{ $tag->name }}</span>
          </label>
        @endforeach
      </div>
      <div class="tag-picker-empty js-tag-picker-empty d-none">Không tìm thấy tag phù hợp.</div>
    </div>
  </div>
  <div class="col-md-6">
    <label class="form-label">Thương hiệu</label>
    <input type="text" name="brand" class="form-control" value="{{ old('brand', $product->brand ?? '') }}">
    @error('brand')
      <div class="text-danger small mt-1">{{ $message }}</div>
    @enderror
  </div>
  <div class="col-md-6">
    <label class="form-label">SKU</label>
    <input type="text" name="sku" class="form-control" value="{{ old('sku', $product->sku ?? '') }}">
    @error('sku')
      <div class="text-danger small mt-1">{{ $message }}</div>
    @enderror
  </div>
  <div class="col-md-4">
    <label class="form-label">Barcode</label>
    <input type="text" name="barcode" class="form-control" value="{{ old('barcode', $product->barcode ?? '') }}">
    @error('barcode')
      <div class="text-danger small mt-1">{{ $message }}</div>
    @enderror
  </div>
  <div class="col-md-4">
    <label class="form-label">Trạng thái</label>
    <select name="status" class="form-select">
      @foreach (['active','hidden','draft'] as $st)
        <option value="{{ $st }}" {{ old('status', $product->status ?? 'active') === $st ? 'selected' : '' }}>{{ $st }}</option>
      @endforeach
    </select>
  </div>
  <div class="col-md-4">
    <label class="form-label d-block">Sản phẩm thông dụng</label>
    <div class="form-check form-switch mt-2">
      <input type="checkbox" name="is_featured" value="1" class="form-check-input" id="is_featured" {{ old('is_featured', isset($product) ? (int) $product->is_featured : 0) ? 'checked' : '' }}>
      <label class="form-check-label" for="is_featured">Hiển thị trong khu vực sản phẩm thông dụng</label>
    </div>
  </div>

  <div class="col-md-3">
    <label class="form-label">Tồn kho</label>
    <input type="number" name="stock_qty" class="form-control" value="{{ old('stock_qty', $product->stock_qty ?? 0) }}">
  </div>
  <div class="col-md-3">
    <label class="form-label">Khối lượng (gram)</label>
    <input type="number" name="weight_gram" class="form-control" value="{{ old('weight_gram', $product->weight_gram ?? '') }}">
  </div>
  <div class="col-md-3">
    <label class="form-label">Đã bán</label>
    <input type="number" name="sold_count" class="form-control" value="{{ old('sold_count', $product->sold_count ?? 0) }}">
  </div>
  <div class="col-md-3">
    <label class="form-label">Lượt xem</label>
    <input type="number" name="view_count" class="form-control" value="{{ old('view_count', $product->view_count ?? 0) }}">
  </div>
  <div class="col-md-3">
    <label class="form-label">Rating TB</label>
    <input type="number" step="0.01" name="rating_avg" class="form-control" value="{{ old('rating_avg', $product->rating_avg ?? 0) }}">
  </div>
  <div class="col-md-3">
    <label class="form-label">Số đánh giá</label>
    <input type="number" name="rating_count" class="form-control" value="{{ old('rating_count', $product->rating_count ?? 0) }}">
  </div>
  <div class="col-md-12">
    <label class="form-label">Mô tả</label>
    <textarea name="description" class="form-control" rows="3">{{ old('description', $product->description ?? '') }}</textarea>
  </div>
  <div class="col-md-12">
    <label class="form-label">Hình ảnh sản phẩm (chọn nhiều)</label>
    <div class="border border-2 border-secondary-subtle rounded-3 p-4 text-center bg-light-subtle js-product-image-dropzone" tabindex="0" role="button" aria-label="Khu vực tải ảnh sản phẩm">
      <div class="fw-semibold mb-2">Kéo thả ảnh vào đây, dán ảnh bằng Ctrl+V, hoặc bấm để chọn file</div>
      <div class="text-secondary small mb-3">Hỗ trợ chọn nhiều ảnh cùng lúc. Ảnh mới sẽ được tự giảm dung lượng trước khi lưu để website tải nhẹ hơn.</div>
      <button type="button" class="btn btn-outline-primary btn-sm js-product-image-browse">Chọn ảnh</button>
      <input type="file" name="images[]" class="form-control d-none js-product-image-input" multiple accept="image/*">
    </div>
    <div class="small text-secondary mt-2 js-product-image-status">Chưa có ảnh mới nào được chọn.</div>
    @error('images')
      <div class="text-danger small mt-1">{{ $message }}</div>
    @enderror
    @error('images.*')
      <div class="text-danger small mt-1">{{ $message }}</div>
    @enderror
    <div class="row g-2 mt-2 js-product-image-preview"></div>
  </div>

  @if(isset($productImages) && $productImages->count() > 0)
    <div class="col-md-12">
      <label class="form-label">Ảnh hiện có (tick để xóa khi cập nhật)</label>
      <div class="row g-2 js-existing-product-images">
        @foreach($productImages as $img)
          <div class="col-md-3 col-6 js-existing-image-item" draggable="true" data-image-id="{{ $img->id }}">
            <input type="hidden" name="image_sort_order[]" value="{{ $img->id }}">
            <div class="border rounded p-2 h-100 bg-white">
              <div class="d-flex justify-content-between align-items-center small text-secondary mb-2">
                <span class="js-existing-image-position">#{{ $loop->iteration }}</span>
                <span class="badge {{ $loop->first ? 'bg-primary' : 'bg-secondary' }} js-existing-image-primary">{{ $loop->first ? 'Ảnh chính' : 'Ảnh phụ' }}</span>
              </div>
              <div class="ratio ratio-1x1 bg-light mb-2">
                <img src="{{ $img->image_url }}" alt="product image" class="w-100 h-100 object-fit-cover rounded">
              </div>
              <div class="small text-secondary mb-2">Kéo thả để đổi vị trí.</div>
              <div class="form-check">
                <input class="form-check-input" type="checkbox" name="delete_image_ids[]" value="{{ $img->id }}" id="delete_image_{{ $img->id }}">
                <label class="form-check-label" for="delete_image_{{ $img->id }}">Xóa ảnh này</label>
              </div>
            </div>
          </div>
        @endforeach
      </div>
    </div>
  @endif
</div>

@once
  @push('scripts')
    <script>
      document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('.js-tag-picker').forEach(function (picker) {
          const searchInput = picker.querySelector('.js-tag-picker-search');
          const selectedContainer = picker.querySelector('.js-tag-picker-selected');
          const items = Array.from(picker.querySelectorAll('.js-tag-picker-item'));
          const emptyState = picker.querySelector('.js-tag-picker-empty');

          function getCheckbox(item) {
            return item.querySelector('.js-tag-picker-checkbox');
          }

          function syncItemState(item) {
            const checkbox = getCheckbox(item);
            item.classList.toggle('is-selected', !!(checkbox && checkbox.checked));
          }

          function renderSelectedTags() {
            const selectedItems = items.filter(function (item) {
              const checkbox = getCheckbox(item);
              return checkbox && checkbox.checked;
            });

            selectedContainer.innerHTML = '';

            if (selectedItems.length === 0) {
              const emptyChip = document.createElement('div');
              emptyChip.className = 'tag-picker-selected-empty';
              emptyChip.textContent = 'Chưa chọn tag nào.';
              selectedContainer.appendChild(emptyChip);
              return;
            }

            selectedItems.forEach(function (item) {
              const checkbox = getCheckbox(item);
              const chip = document.createElement('button');
              chip.type = 'button';
              chip.className = 'tag-picker-chip';
              chip.dataset.tagId = checkbox.dataset.tagId || '';
              chip.innerHTML = '<span>' + checkbox.dataset.tagLabel + '</span><i class="bi bi-x"></i>';
              chip.addEventListener('click', function () {
                checkbox.checked = false;
                syncItemState(item);
                renderSelectedTags();
                applyFilter();
              });
              selectedContainer.appendChild(chip);
            });
          }

          function applyFilter() {
            const keyword = (searchInput.value || '').trim().toLowerCase();
            let visibleCount = 0;

            items.forEach(function (item) {
              const tagSearch = (item.dataset.tagSearch || '').toLowerCase();
              const isMatch = keyword === '' || tagSearch.indexOf(keyword) !== -1;
              item.classList.toggle('d-none', !isMatch);
              if (isMatch) {
                visibleCount += 1;
              }
            });

            emptyState.classList.toggle('d-none', visibleCount === 0 ? false : true);
          }

          items.forEach(function (item) {
            const checkbox = getCheckbox(item);
            if (!checkbox) {
              return;
            }

            syncItemState(item);
            checkbox.addEventListener('change', function () {
              syncItemState(item);
              renderSelectedTags();
            });
          });

          if (searchInput) {
            searchInput.addEventListener('input', applyFilter);
          }

          renderSelectedTags();
          applyFilter();
        });

        document.querySelectorAll('form').forEach(function (form) {
          const colorSelect = form.querySelector('select[name="color_ids[]"]');
          const mapWrap = form.querySelector('.js-color-image-map-wrap');

          if (!colorSelect || !mapWrap) {
            return;
          }

          const colorItems = Array.from(mapWrap.querySelectorAll('.js-color-image-map-item'));
          const colorImageSelects = Array.from(mapWrap.querySelectorAll('.js-color-image-select'));
          let selectedNewUploadFiles = [];

          const getSelectedColorIds = function () {
            return Array.from(colorSelect.options || [])
              .filter(function (option) {
                return option.selected;
              })
              .map(function (option) {
                return String(option.value || '');
              })
              .filter(Boolean);
          };

          const syncVisibleColorRows = function () {
            const selectedIds = getSelectedColorIds();
            const selectedLookup = new Set(selectedIds);

            colorItems.forEach(function (item) {
              const colorId = String(item.dataset.colorId || '');
              const isVisible = selectedLookup.has(colorId);
              item.classList.toggle('d-none', !isVisible);

              const select = item.querySelector('.js-color-image-select');
              if (!isVisible && select) {
                select.value = '';
                const preview = item.querySelector('.js-color-image-preview');
                if (preview) {
                  preview.classList.add('d-none');
                  const previewImg = preview.querySelector('img');
                  if (previewImg) {
                    previewImg.src = '';
                  }
                }
              }
            });
          };

          const syncColorImagePreview = function (selectElement) {
            const select = selectElement;
            if (!select) {
              return;
            }

            const colorId = String(select.dataset.colorId || '');
            const preview = mapWrap.querySelector('[data-color-preview="' + colorId + '"]');
            if (!preview) {
              return;
            }

            const selectedUrl = String(select.value || '').trim();
            const previewImg = preview.querySelector('img');
            if (!previewImg) {
              return;
            }

            if (previewImg.dataset.tempObjectUrl) {
              URL.revokeObjectURL(previewImg.dataset.tempObjectUrl);
              delete previewImg.dataset.tempObjectUrl;
            }

            if (selectedUrl === '') {
              preview.classList.add('d-none');
              previewImg.src = '';
              return;
            }

            if (selectedUrl.startsWith('__new__:')) {
              const selectedIndex = Number((selectedUrl.split(':')[1] || '').trim());
              const selectedFile = Number.isInteger(selectedIndex) && selectedIndex >= 0
                ? (selectedNewUploadFiles[selectedIndex] || null)
                : null;

              if (!selectedFile) {
                preview.classList.add('d-none');
                previewImg.src = '';
                return;
              }

              const objectUrl = URL.createObjectURL(selectedFile);
              preview.classList.remove('d-none');
              previewImg.src = objectUrl;
              previewImg.dataset.tempObjectUrl = objectUrl;
              return;
            }

            preview.classList.remove('d-none');
            previewImg.src = selectedUrl.startsWith('http://') || selectedUrl.startsWith('https://')
              ? selectedUrl
              : (window.location.origin + (selectedUrl.startsWith('/') ? selectedUrl : ('/' + selectedUrl)));
          };

          const syncColorImageSelectOptions = function (nextFiles) {
            selectedNewUploadFiles = Array.isArray(nextFiles) ? nextFiles.slice() : [];

            colorImageSelects.forEach(function (select) {
              const currentValue = String(select.value || '');

              Array.from(select.querySelectorAll('option[data-new-upload-option="1"]')).forEach(function (option) {
                option.remove();
              });

              selectedNewUploadFiles.forEach(function (file, index) {
                const option = document.createElement('option');
                option.value = '__new__:' + index;
                option.setAttribute('data-new-upload-option', '1');
                option.textContent = 'Anh moi #' + (index + 1) + ' - ' + (file && file.name ? file.name : 'image');
                select.appendChild(option);
              });

              const hasCurrentValue = Array.from(select.options).some(function (option) {
                return String(option.value || '') === currentValue;
              });

              if (hasCurrentValue) {
                select.value = currentValue;
              } else if (currentValue.startsWith('__new__:')) {
                select.value = '';
              }

              syncColorImagePreview(select);
            });
          };

          form.addEventListener('shopnoiy:product-images-updated', function (event) {
            const files = event && event.detail && Array.isArray(event.detail.files)
              ? event.detail.files
              : [];
            syncColorImageSelectOptions(files);
          });

          colorImageSelects.forEach(function (select) {
            select.addEventListener('change', function () {
              syncColorImagePreview(select);
            });
            syncColorImagePreview(select);
          });

          colorSelect.addEventListener('change', syncVisibleColorRows);
          if (typeof window.jQuery !== 'undefined') {
            window.jQuery(colorSelect).on('change.select2_color_map', syncVisibleColorRows);
          }
          syncVisibleColorRows();

          form.__shopNoiySyncColorImageSelectOptions = syncColorImageSelectOptions;
        });

        document.querySelectorAll('.js-existing-product-images').forEach(function (container) {
          let draggingItem = null;

          function syncExistingImageState() {
            Array.from(container.querySelectorAll('.js-existing-image-item')).forEach(function (item, index) {
              const position = item.querySelector('.js-existing-image-position');
              const primaryBadge = item.querySelector('.js-existing-image-primary');

              if (position) {
                position.textContent = '#' + (index + 1);
              }

              if (primaryBadge) {
                primaryBadge.textContent = index === 0 ? 'Ảnh chính' : 'Ảnh phụ';
                primaryBadge.classList.toggle('bg-primary', index === 0);
                primaryBadge.classList.toggle('bg-secondary', index !== 0);
              }
            });
          }

          function resolveItem(target) {
            const item = target.closest('.js-existing-image-item');
            return item && container.contains(item) ? item : null;
          }

          container.addEventListener('dragstart', function (event) {
            const item = resolveItem(event.target);
            if (!item) {
              return;
            }

            draggingItem = item;
            item.classList.add('opacity-50');

            if (event.dataTransfer) {
              event.dataTransfer.effectAllowed = 'move';
              event.dataTransfer.setData('text/plain', item.dataset.imageId || '');
            }
          });

          container.addEventListener('dragend', function () {
            if (draggingItem) {
              draggingItem.classList.remove('opacity-50');
            }

            draggingItem = null;
            syncExistingImageState();
          });

          container.addEventListener('dragover', function (event) {
            if (!draggingItem) {
              return;
            }

            event.preventDefault();

            const targetItem = resolveItem(event.target);
            if (!targetItem || targetItem === draggingItem) {
              return;
            }

            const targetRect = targetItem.getBoundingClientRect();
            const insertAfter = event.clientY > targetRect.top + (targetRect.height / 2);
            container.insertBefore(draggingItem, insertAfter ? targetItem.nextElementSibling : targetItem);
          });

          container.addEventListener('drop', function (event) {
            if (!draggingItem) {
              return;
            }

            event.preventDefault();
            syncExistingImageState();
          });

          syncExistingImageState();
        });

        document.querySelectorAll('.js-product-image-dropzone').forEach(function (dropzone) {
          const form = dropzone.closest('form');
          const input = dropzone.querySelector('.js-product-image-input');
          const browseButton = dropzone.querySelector('.js-product-image-browse');
          const status = dropzone.parentElement.querySelector('.js-product-image-status');
          const preview = dropzone.parentElement.querySelector('.js-product-image-preview');

          if (!form || !input || !preview || typeof DataTransfer === 'undefined') {
            return;
          }

          let selectedFiles = Array.from(input.files || []);
          let isDropzoneActive = false;
          let resumeSubmit = false;
          const activeOptimizationTasks = new Set();
          const MAX_IMAGE_DIMENSION = 1600;
          const JPEG_QUALITY = 0.82;
          const WEBP_QUALITY = 0.8;
          const OPTIMIZABLE_TYPES = ['image/jpeg', 'image/png', 'image/webp'];
          const supportsWebp = (function () {
            try {
              const canvas = document.createElement('canvas');
              return canvas.toDataURL('image/webp').indexOf('data:image/webp') === 0;
            } catch (error) {
              return false;
            }
          })();

          function formatBytes(bytes) {
            const value = Number(bytes) || 0;
            if (value <= 0) {
              return '0 KB';
            }

            if (value >= 1024 * 1024) {
              return (value / (1024 * 1024)).toFixed(2).replace(/\.00$/, '') + ' MB';
            }

            return Math.max(1, Math.round(value / 1024)) + ' KB';
          }

          function getOptimizationStats() {
            return selectedFiles.reduce(function (stats, file) {
              const meta = file && file.shopNoiyOptimization ? file.shopNoiyOptimization : null;
              stats.optimizedBytes += file && file.size ? file.size : 0;
              stats.originalBytes += meta && meta.originalSize ? meta.originalSize : (file && file.size ? file.size : 0);
              stats.optimizedCount += meta && meta.wasOptimized ? 1 : 0;
              return stats;
            }, {
              originalBytes: 0,
              optimizedBytes: 0,
              optimizedCount: 0
            });
          }

          function updateStatus() {
            if (!status) {
              return;
            }

            if (activeOptimizationTasks.size > 0) {
              status.textContent = 'Đang tối ưu ảnh trước khi tải lên...';
              return;
            }

            if (selectedFiles.length === 0) {
              status.textContent = 'Chưa có ảnh mới nào được chọn.';
              return;
            }

            const stats = getOptimizationStats();
            let statusText = 'Đã chọn ' + selectedFiles.length + ' ảnh mới';

            if (stats.optimizedCount > 0 && stats.originalBytes > stats.optimizedBytes) {
              const savedRatio = Math.max(0, Math.round((1 - (stats.optimizedBytes / stats.originalBytes)) * 100));
              statusText += ' • giảm từ ' + formatBytes(stats.originalBytes) + ' còn ' + formatBytes(stats.optimizedBytes) + ' (' + savedRatio + '%)';
            } else {
              statusText += ' • tổng dung lượng ' + formatBytes(stats.optimizedBytes);
            }

            status.textContent = statusText + '.';
          }

          function syncInputFiles() {
            const transfer = new DataTransfer();
            selectedFiles.forEach(function (file) {
              transfer.items.add(file);
            });
            input.files = transfer.files;
          }

          function renderPreview() {
            preview.innerHTML = '';
            updateStatus();
            form.dispatchEvent(new CustomEvent('shopnoiy:product-images-updated', {
              detail: {
                files: selectedFiles
              }
            }));

            if (selectedFiles.length === 0) {
              return;
            }

            selectedFiles.forEach(function (file, index) {
              const col = document.createElement('div');
              col.className = 'col-md-3 col-6';

              const card = document.createElement('div');
              card.className = 'border rounded p-2 h-100 bg-white';

              const ratio = document.createElement('div');
              ratio.className = 'ratio ratio-1x1 bg-light rounded overflow-hidden mb-2';

              const image = document.createElement('img');
              image.className = 'w-100 h-100 object-fit-cover';
              image.alt = file.name;
              image.src = URL.createObjectURL(file);
              image.addEventListener('load', function () {
                URL.revokeObjectURL(image.src);
              });

              const name = document.createElement('div');
              name.className = 'small text-truncate mb-2';
              name.title = file.name;
              name.textContent = file.name;

              const meta = file.shopNoiyOptimization || null;
              const details = document.createElement('div');
              details.className = 'small text-secondary mb-2';

              if (meta && meta.wasOptimized && meta.originalSize > file.size) {
                const savedRatio = Math.max(0, Math.round((1 - (file.size / meta.originalSize)) * 100));
                details.textContent = formatBytes(file.size) + ' • từ ' + formatBytes(meta.originalSize) + ' • giảm ' + savedRatio + '%';
              } else {
                details.textContent = formatBytes(file.size);
              }

              const removeButton = document.createElement('button');
              removeButton.type = 'button';
              removeButton.className = 'btn btn-outline-danger btn-sm w-100';
              removeButton.textContent = 'Xóa khỏi danh sách';
              removeButton.addEventListener('click', function () {
                selectedFiles = selectedFiles.filter(function (_, fileIndex) {
                  return fileIndex !== index;
                });
                syncInputFiles();
                renderPreview();
              });

              ratio.appendChild(image);
              card.appendChild(ratio);
              card.appendChild(name);
              card.appendChild(details);
              card.appendChild(removeButton);
              col.appendChild(card);
              preview.appendChild(col);
            });
          }

          function createNamedFile(file, index, forceExtension) {
            if (!(file instanceof File)) {
              return null;
            }

            const extensionFromType = forceExtension || (file.type || '').split('/')[1] || 'png';
            const safeExtension = extensionFromType.replace(/[^a-z0-9]/gi, '').toLowerCase() || 'png';
            const baseName = file.name && file.name.trim() !== ''
              ? file.name.replace(/\.[^.]+$/, '')
              : 'clipboard-' + Date.now() + '-' + index;
            const fileName = baseName + '.' + safeExtension;

            return new File([file], fileName, {
              type: file.type || 'image/png',
              lastModified: Date.now()
            });
          }

          function loadImageFromFile(file) {
            return new Promise(function (resolve, reject) {
              const objectUrl = URL.createObjectURL(file);
              const image = new Image();
              image.onload = function () {
                URL.revokeObjectURL(objectUrl);
                resolve(image);
              };
              image.onerror = function () {
                URL.revokeObjectURL(objectUrl);
                reject(new Error('Không thể đọc ảnh tải lên.'));
              };
              image.src = objectUrl;
            });
          }

          function canvasToBlob(canvas, type, quality) {
            return new Promise(function (resolve, reject) {
              canvas.toBlob(function (blob) {
                if (!blob) {
                  reject(new Error('Không thể tạo dữ liệu ảnh mới.'));
                  return;
                }

                resolve(blob);
              }, type, quality);
            });
          }

          async function optimizeImageFile(file, index) {
            const namedFile = createNamedFile(file, index);
            if (!namedFile) {
              return null;
            }

            if (!OPTIMIZABLE_TYPES.includes(namedFile.type)) {
              namedFile.shopNoiyOptimization = {
                originalSize: namedFile.size,
                wasOptimized: false,
              };
              return namedFile;
            }

            try {
              const image = await loadImageFromFile(namedFile);
              const width = image.naturalWidth || image.width || 0;
              const height = image.naturalHeight || image.height || 0;

              if (width <= 0 || height <= 0) {
                namedFile.shopNoiyOptimization = {
                  originalSize: namedFile.size,
                  wasOptimized: false,
                };
                return namedFile;
              }

              const scale = Math.min(1, MAX_IMAGE_DIMENSION / Math.max(width, height));
              const targetWidth = Math.max(1, Math.round(width * scale));
              const targetHeight = Math.max(1, Math.round(height * scale));
              const canvas = document.createElement('canvas');
              canvas.width = targetWidth;
              canvas.height = targetHeight;

              const context = canvas.getContext('2d', { alpha: true });
              if (!context) {
                namedFile.shopNoiyOptimization = {
                  originalSize: namedFile.size,
                  wasOptimized: false,
                };
                return namedFile;
              }

              context.drawImage(image, 0, 0, targetWidth, targetHeight);

              let targetType = namedFile.type === 'image/jpeg' ? 'image/jpeg' : 'image/webp';
              if (targetType === 'image/webp' && !supportsWebp) {
                targetType = namedFile.type === 'image/png' ? 'image/png' : 'image/jpeg';
              }

              const targetQuality = targetType === 'image/jpeg' ? JPEG_QUALITY : WEBP_QUALITY;
              const blob = await canvasToBlob(canvas, targetType, targetQuality);

              if (blob.size >= namedFile.size && scale === 1) {
                namedFile.shopNoiyOptimization = {
                  originalSize: namedFile.size,
                  wasOptimized: false,
                };
                return namedFile;
              }

              const targetExtension = targetType === 'image/jpeg'
                ? 'jpg'
                : (targetType === 'image/webp' ? 'webp' : 'png');

              const optimizedFile = new File([blob], (namedFile.name || 'image').replace(/\.[^.]+$/, '') + '.' + targetExtension, {
                type: targetType,
                lastModified: Date.now()
              });

              optimizedFile.shopNoiyOptimization = {
                originalSize: namedFile.size,
                wasOptimized: blob.size < namedFile.size || scale < 1,
              };

              return optimizedFile;
            } catch (error) {
              namedFile.shopNoiyOptimization = {
                originalSize: namedFile.size,
                wasOptimized: false,
              };
              return namedFile;
            }
          }

          async function appendFiles(files) {
            const imageFiles = Array.from(files || [])
              .filter(function (file) {
                return file && typeof file.type === 'string' && file.type.startsWith('image/');
              })
              .filter(Boolean);

            if (imageFiles.length === 0) {
              return;
            }

            const optimizationTask = Promise.all(imageFiles.map(function (file, index) {
              return optimizeImageFile(file, selectedFiles.length + index);
            })).then(function (optimizedFiles) {
              const validFiles = optimizedFiles.filter(Boolean);
              if (validFiles.length === 0) {
                return;
              }

              selectedFiles = selectedFiles.concat(validFiles);
              syncInputFiles();
              renderPreview();
            }).finally(function () {
              activeOptimizationTasks.delete(optimizationTask);
              updateStatus();
            });

            activeOptimizationTasks.add(optimizationTask);
            updateStatus();
            await optimizationTask;
          }

          if (browseButton) {
            browseButton.addEventListener('click', function (event) {
              event.preventDefault();
              input.click();
            });
          }

          dropzone.addEventListener('click', function (event) {
            if (event.target === input || event.target === browseButton) {
              return;
            }

            input.click();
          });

          dropzone.addEventListener('keydown', function (event) {
            if (event.key === 'Enter' || event.key === ' ') {
              event.preventDefault();
              input.click();
            }
          });

          dropzone.addEventListener('focus', function () {
            isDropzoneActive = true;
          });

          dropzone.addEventListener('blur', function () {
            isDropzoneActive = false;
          });

          dropzone.addEventListener('mouseenter', function () {
            isDropzoneActive = true;
          });

          dropzone.addEventListener('mouseleave', function () {
            isDropzoneActive = false;
          });

          input.addEventListener('change', function () {
            appendFiles(input.files);
          });

          ['dragenter', 'dragover'].forEach(function (eventName) {
            dropzone.addEventListener(eventName, function (event) {
              event.preventDefault();
              dropzone.classList.add('border-primary', 'bg-primary-subtle');
            });
          });

          ['dragleave', 'dragend', 'drop'].forEach(function (eventName) {
            dropzone.addEventListener(eventName, function (event) {
              event.preventDefault();
              dropzone.classList.remove('border-primary', 'bg-primary-subtle');
            });
          });

          dropzone.addEventListener('drop', function (event) {
            appendFiles(event.dataTransfer ? event.dataTransfer.files : []);
          });

          dropzone.addEventListener('paste', function (event) {
            const clipboardItems = Array.from(event.clipboardData ? event.clipboardData.items : []);
            const files = clipboardItems
              .filter(function (item) {
                return item.kind === 'file' && item.type.startsWith('image/');
              })
              .map(function (item) {
                return item.getAsFile();
              })
              .filter(Boolean);

            if (files.length === 0) {
              return;
            }

            event.preventDefault();
            appendFiles(files);
          });

          form.addEventListener('paste', function (event) {
            const clipboardItems = Array.from(event.clipboardData ? event.clipboardData.items : []);
            const files = clipboardItems
              .filter(function (item) {
                return item.kind === 'file' && item.type.startsWith('image/');
              })
              .map(function (item) {
                return item.getAsFile();
              })
              .filter(Boolean);

            if (files.length === 0) {
              return;
            }

            const activeElement = document.activeElement;
            const isTypingField = activeElement && (
              activeElement.tagName === 'INPUT' ||
              activeElement.tagName === 'TEXTAREA' ||
              activeElement.isContentEditable
            );

            if (!isDropzoneActive && isTypingField) {
              return;
            }

            event.preventDefault();
            appendFiles(files);
            dropzone.focus();
          });

          form.addEventListener('submit', function (event) {
            if (resumeSubmit || activeOptimizationTasks.size === 0) {
              resumeSubmit = false;
              return;
            }

            event.preventDefault();
            updateStatus();

            Promise.allSettled(Array.from(activeOptimizationTasks)).then(function () {
              resumeSubmit = true;
              if (typeof form.requestSubmit === 'function') {
                form.requestSubmit(event.submitter || undefined);
              } else {
                form.submit();
              }
            });
          });

          renderPreview();
        });
      });
    </script>
  @endpush
@endonce

<div class="mt-3 d-flex gap-2">
  <button type="submit" class="btn btn-primary">{{ isset($product) ? 'Cập nhật' : 'Tạo mới' }}</button>
  <a href="{{ route('backend.products') }}" class="btn btn-secondary">Quay lại</a>
</div>
