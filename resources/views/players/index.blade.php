@extends('layouts.app')

@section('title', 'Players')
@section('page-title', 'Players')

@section('content')
    @include('partials.flash')
    @include('partials.modals.player')
    @include('partials.modals.edit-player')

    <div class="card">
        <div class="card-header d-flex align-items-center justify-content-between">
            <h5 class="card-title mb-0">Players ({{ $players->count() }})</h5>
            <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#modalPlayer">
                <i class="ri-user-add-line align-bottom me-1"></i> Registrar Player
            </button>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-sm table-striped align-middle mb-0">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Imagen</th>
                            <th>Nombre</th>
                            <th>Correo</th>
                            <th class="text-end">Victorias</th>
                            <th class="text-end">Derrotas</th>
                            <th class="text-end">Puntos</th>
                            <th class="text-end">Accion</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($players as $player)
                            <tr>
                                <td>{{ $player->id }}</td>
                                <td>
                                    @if ($player->image)
                                        <img src="{{ $player->image->url }}" alt="{{ $player->image->nombre }}" class="rounded-circle" style="width: 36px; height: 36px; object-fit: cover;" loading="lazy">
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </td>
                                <td>{{ $player->nombre }}</td>
                                <td>{{ $player->email ?? '—' }}</td>
                                <td class="text-end">{{ $player->victorias }}</td>
                                <td class="text-end">{{ $player->derrotas }}</td>
                                <td class="text-end">{{ $player->puntos }}</td>
                                <td class="text-end">
                                    <button type="button" class="btn btn-sm btn-soft-primary btn-editar-player"
                                        data-action="{{ route('players.update', $player) }}"
                                        data-nombre="{{ $player->nombre }}"
                                        data-email="{{ $player->email }}"
                                        data-image-id="{{ $player->image_id }}">
                                        Editar
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center text-muted">No hay players registrados.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        document.querySelectorAll('.btn-editar-player').forEach(function (button) {
            button.addEventListener('click', function () {
                document.getElementById('editPlayerForm').action = button.dataset.action;
                document.getElementById('editPlayerNombre').value = button.dataset.nombre;
                document.getElementById('editPlayerEmail').value = button.dataset.email || '';
                var selectedImage = button.dataset.imageId || '';
                document.querySelectorAll('#editPlayerForm input[name=image_id]').forEach(function (radio) {
                    radio.checked = radio.value === selectedImage;
                });
                bootstrap.Modal.getOrCreateInstance(document.getElementById('modalEditarPlayer')).show();
            });
        });
    </script>
@endpush
