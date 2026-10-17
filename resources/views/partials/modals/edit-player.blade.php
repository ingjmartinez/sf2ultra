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
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary">Guardar</button>
                </div>
            </form>
        </div>
    </div>
</div>
