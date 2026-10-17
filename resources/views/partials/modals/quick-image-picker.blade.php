<div class="mt-2" data-quick-image-picker="{{ $slot }}">
    <div class="form-text mb-1">Personaje (opcional)</div>
    <div class="d-flex gap-2 overflow-auto pb-1">
        <label class="border rounded p-1 text-center flex-shrink-0" style="width: 64px; cursor: pointer;">
            <input class="form-check-input d-block mx-auto mb-1" type="radio" name="quick_imagen_{{ $slot }}" value="" checked>
            <span class="small text-muted">Ninguno</span>
        </label>
        @foreach ($images as $image)
            <label class="border rounded p-1 text-center flex-shrink-0" style="width: 64px; cursor: pointer;" title="{{ $image->nombre }}">
                <input class="form-check-input d-block mx-auto mb-1" type="radio" name="quick_imagen_{{ $slot }}" value="{{ $image->id }}">
                <img src="{{ $image->url }}" alt="{{ $image->nombre }}" class="rounded" style="width: 100%; aspect-ratio: 1; object-fit: cover;" loading="lazy">
            </label>
        @endforeach
    </div>
</div>
