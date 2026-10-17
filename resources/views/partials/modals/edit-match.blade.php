<div class="modal fade match-modal" id="modalEditarMatch" tabindex="-1" aria-labelledby="modalEditarMatchLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-fullscreen-sm-down">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalEditarMatchLabel">Editar Match</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="post" action="" id="editMatchForm">
                @csrf
                @method('PUT')
                <input type="hidden" name="season" value="{{ $season }}">
                <div class="modal-body">
                    <input type="hidden" name="ganador_id" id="editGanadorMatch">
                    <input type="hidden" name="perdedor_id" id="editPerdedorMatch">

                    <div class="match-summary mb-3">
                        <div>
                            <div class="text-muted small text-uppercase">Match</div>
                            <div id="editMatchPlayers">Ganador vs Perdedor</div>
                        </div>
                        <div class="text-muted small" id="editMatchDate"></div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label" for="editFechaMatch">Fecha y hora</label>
                        <input class="form-control form-control-lg" id="editFechaMatch" type="datetime-local" name="fecha" required>
                    </div>

                    <div class="row g-3 mt-1">
                        <div class="col-12 col-md-6">
                            <label class="form-label" for="editVictoriasGanador">Victorias ganador</label>
                            <select class="form-select form-select-lg" id="editVictoriasGanador" name="victorias_ganador" required>
                                <option value="5">5</option>
                                <option value="3">3</option>
                            </select>
                        </div>

                        <div class="col-12 col-md-6">
                            <label class="form-label" for="editVictoriasPerdedor">Victorias perdedor</label>
                            <input class="form-control form-control-lg" id="editVictoriasPerdedor" type="number" name="victorias_perdedor" min="0" max="4" required>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary btn-lg">Guardar Cambios</button>
                </div>
            </form>
        </div>
    </div>
</div>
