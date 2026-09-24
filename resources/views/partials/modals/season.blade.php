<div class="modal fade" id="modalTemporada" tabindex="-1" aria-labelledby="modalTemporadaLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalTemporadaLabel">Seleccionar Temporada</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="get" action="{{ route('dashboard') }}">
                <div class="modal-body">
                    <label class="form-label" for="season">Temporada</label>
                    <select class="form-select form-select-lg" id="season" name="season" required>
                        @foreach ($availableSeasons as $availableSeason)
                            <option value="{{ $availableSeason }}" @selected((string) $availableSeason === (string) $season)>
                                Season {{ $availableSeason }}
                            </option>
                        @endforeach
                    </select>
                    <div class="form-text">El ranking, las estadisticas y la tabla de matches se filtran por el ano de la fecha registrada.</div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-warning btn-lg">Aplicar Temporada</button>
                </div>
            </form>
        </div>
    </div>
</div>
