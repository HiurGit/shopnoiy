<div class="row g-3">
  <div class="col-md-6">
    <label class="form-label">Ten ticker</label>
    <input type="text" name="name" class="form-control" value="{{ old('name', $promoTicker->name ?? '') }}">
  </div>
  <div class="col-md-6">
    <label class="form-label">Promotion</label>
    <select name="promotion_id" class="form-select">
      <option value="">--</option>
      @foreach($promotions as $promotion)
        <option value="{{ $promotion->id }}" {{ (string) old('promotion_id', $promoTicker->promotion_id ?? '') === (string) $promotion->id ? 'selected' : '' }}>{{ $promotion->name }}</option>
      @endforeach
    </select>
  </div>
  <div class="col-md-12">
    <label class="form-label">Noi dung</label>
    <textarea name="content_text" class="form-control" rows="3">{{ old('content_text', $promoTicker->content_text ?? '') }}</textarea>
  </div>
  <div class="col-md-4">
    <label class="form-label">Background style</label>
    <input type="text" name="background_style" class="form-control" value="{{ old('background_style', $promoTicker->background_style ?? '') }}">
  </div>
  <div class="col-md-4">
    <label class="form-label">Text color</label>
    <input type="text" name="text_color" class="form-control" value="{{ old('text_color', $promoTicker->text_color ?? '') }}">
  </div>
  <div class="col-md-4">
    <label class="form-label">Toc do</label>
    <input type="number" name="speed_seconds" class="form-control" value="{{ old('speed_seconds', $promoTicker->speed_seconds ?? 18) }}">
  </div>
  <div class="col-md-6">
    <label class="form-label">Bat dau</label>
    <input type="datetime-local" name="start_at" class="form-control" value="{{ old('start_at', isset($promoTicker) && $promoTicker->start_at ? \Carbon\Carbon::parse($promoTicker->start_at)->format('Y-m-d\TH:i') : '') }}">
  </div>
  <div class="col-md-6">
    <label class="form-label">Ket thuc</label>
    <input type="datetime-local" name="end_at" class="form-control" value="{{ old('end_at', isset($promoTicker) && $promoTicker->end_at ? \Carbon\Carbon::parse($promoTicker->end_at)->format('Y-m-d\TH:i') : '') }}">
  </div>
  <div class="col-md-4">
    <label class="form-label">Trang thai</label>
    <input type="text" name="status" class="form-control" value="{{ old('status', $promoTicker->status ?? 'active') }}">
  </div>
</div>
<div class="mt-3 d-flex gap-2">
  <button class="btn btn-primary" type="submit">{{ isset($promoTicker) ? 'Cap nhat' : 'Tao moi' }}</button>
  <a href="{{ route('backend.promo-tickers') }}" class="btn btn-secondary">Quay lai</a>
</div>
