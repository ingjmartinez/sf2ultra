<div class="modal fade" id="modalPlayer" tabindex="-1" aria-labelledby="modalPlayerLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalPlayerLabel">Registrar Player</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="post" action="{{ route('players.store') }}">
                @csrf
                <div class="modal-body">
                    <label class="form-label" for="playerNombre">Nombre</label>
                    <input class="form-control" id="playerNombre" type="text" name="nombre" maxlength="100" required>

                    <label class="form-label mt-3" for="playerEmail">Correo (opcional)</label>
                    <input class="form-control" id="playerEmail" type="email" name="email" maxlength="255">
                    <div class="form-text">Recibirá un aviso cuando se registre un match suyo.</div>
                    <label class="form-label mt-3" for="playerPassword">Contrasena de acceso (opcional)</label>
                    <input class="form-control" id="playerPassword" type="password" name="password" minlength="8" maxlength="72" autocomplete="new-password">
                    <div class="form-text">Minimo 8 caracteres. Con contrasena y correo el jugador podra iniciar sesion.</div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary">Guardar</button>
                </div>
            </form>
        </div>
    </div>
</div>
