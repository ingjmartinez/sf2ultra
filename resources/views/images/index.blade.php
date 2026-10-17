@extends('layouts.app')

@section('title', 'Imagenes')
@section('page-title', 'Imagenes')

@section('content')
    @include('partials.flash')

    <div class="card">
        <div class="card-header">
            <h5 class="card-title mb-0">Cargar imagen</h5>
        </div>
        <div class="card-body">
            <form method="post" action="{{ route('imagenes.store') }}" enctype="multipart/form-data" class="row g-3 align-items-end">
                @csrf
                <div class="col-12 col-md-5">
                    <label class="form-label" for="imagenArchivo">Imagen</label>
                    <input class="form-control @error('imagen') is-invalid @enderror" id="imagenArchivo" type="file" name="imagen" accept="image/*" required>
                    @error('imagen')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                    <div class="form-text">jpg, png, webp o gif. Maximo 10 MB.</div>
                </div>
                <div class="col-12 col-md-4">
                    <label class="form-label" for="imagenNombre">Nombre (opcional)</label>
                    <input class="form-control @error('nombre') is-invalid @enderror" id="imagenNombre" type="text" name="nombre" maxlength="100" value="{{ old('nombre') }}">
                    @error('nombre')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-12 col-md-3">
                    <button type="submit" class="btn btn-primary w-100"><i class="ri-upload-2-line align-bottom me-1"></i> Cargar</button>
                </div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h5 class="card-title mb-0">Galeria ({{ $images->count() }})</h5>
        </div>
        <div class="card-body">
            <div class="row row-cols-2 row-cols-md-4 row-cols-xl-6 g-3">
                @forelse ($images as $image)
                    <div class="col">
                        <div class="border rounded p-2 h-100 text-center">
                            <img src="{{ $image->url }}" alt="{{ $image->nombre }}" class="img-fluid rounded mb-2" style="aspect-ratio: 1; object-fit: cover; width: 100%;" loading="lazy">
                            <div class="small fw-semibold text-truncate">{{ $image->nombre }}</div>
                            <div class="small text-muted mb-2">{{ $image->players_count }} {{ $image->players_count === 1 ? 'player' : 'players' }}</div>
                            <form method="post" action="{{ route('imagenes.destroy', $image) }}" onsubmit="return confirm('¿Eliminar esta imagen? Los players que la usen quedaran sin imagen.')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-soft-danger w-100">Eliminar</button>
                            </form>
                        </div>
                    </div>
                @empty
                    <div class="col-12 text-center text-muted">No hay imagenes cargadas.</div>
                @endforelse
            </div>
        </div>
    </div>
@endsection
