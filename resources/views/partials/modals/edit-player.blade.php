<div class="modal fade" id="modalEditarPlayer" tabindex="-1" aria-labelledby="modalEditarPlayerLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalEditarPlayerLabel">Editar Player</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="post" action="" id="editPlayerForm">
                @csrf
                @method('PUT')
                <div class="modal-body">
                    <label class="form-label" for="editPlayerNombre">Nombre</label>
                    <input class="form-control" id="editPlayerNombre" type="text" name="nombre" maxlength="100" required>

                    <label class="form-label mt-3" for="editPlayerEmail">Correo (opcional)</label>
                    <input class="form-control" id="editPlayerEmail" type="email" name="email" maxlength="255">
                    <div class="form-text">Recibirá un aviso cuando se registre un match suyo.</div>
                    <label class="form-label mt-3" for="editPlayerPassword">Contrasena de acceso (opcional)</label>
                    <input class="form-control" id="editPlayerPassword" type="password" name="password" minlength="8" maxlength="72" autocomplete="new-password">
                    <div class="form-text">Dejalo vacio para no cambiarla. Minimo 8 caracteres.</div>
                    <div class="form-text text-success d-none" id="editPlayerAccess"><i class="ri-key-2-line"></i> Este jugador ya tiene acceso a la plataforma.</div>

                    <label class="form-label mt-3">Imagen</label>
                    <div class="d-flex flex-wrap gap-2 player-image-picker">
                        <label class="border rounded p-1 text-center" style="width: 72px; cursor: pointer;">
                            <input class="form-check-input d-block mx-auto mb-1" type="radio" name="image_id" value="" checked>
                            <span class="small text-muted">Ninguna</span>
                        </label>
                        @foreach ($images as $image)
                            <label class="border rounded p-1 text-center" style="width: 72px; cursor: pointer;" title="{{ $image->nombre }}">
                                <input class="form-check-input d-block mx-auto mb-1" type="radio" name="image_id" value="{{ $image->id }}">
                                <img src="{{ $image->url }}" alt="{{ $image->nombre }}" class="rounded" style="width: 100%; aspect-ratio: 1; object-fit: cover;" loading="lazy">
                            </label>
                        @endforeach
                    </div>
                    @if ($images->isEmpty())
                        <div class="form-text">Aun no hay imagenes. Cargalas en el modulo <a href="{{ route('imagenes.index') }}">Imagenes</a>.</div>
                    @endif
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary">Guardar</button>
                </div>
            </form>
        </div>
    </div>
</div>
