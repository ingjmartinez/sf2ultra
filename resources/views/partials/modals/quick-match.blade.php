<div class="modal fade match-modal quick-match-modal" id="modalPleitoMovil" tabindex="-1" aria-labelledby="modalPleitoMovilLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-fullscreen-sm-down">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalPleitoMovilLabel">Registrar Pleito</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="post" action="{{ route('matches.store') }}" id="quickMatchForm">
                @csrf
                <input type="hidden" name="season" value="{{ $season }}">
                <div class="modal-body">
                    <div class="quick-match-setup" id="quickSetupStep">
                        <div class="mb-3">
                            <label class="form-label" for="fechaPleito">Fecha</label>
                            <input class="form-control form-control-lg" id="fechaPleito" type="datetime-local" name="fecha" value="{{ $currentDateTimeLocal }}" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Formato del combate</label>
                            <div class="btn-group w-100" role="group" aria-label="Formato del combate">
                                <input class="btn-check" type="radio" name="formato_pleito" id="pleitoGanar5" value="5" checked>
                                <label class="btn btn-outline-primary btn-lg" for="pleitoGanar5">Ganar 5 manos</label>

                                <input class="btn-check" type="radio" name="formato_pleito" id="pleitoGanar3" value="3">
                                <label class="btn btn-outline-primary btn-lg" for="pleitoGanar3">Ganar 3 manos</label>
                            </div>
                        </div>

                        <div class="row g-3">
                            @foreach ([1, 2] as $slot)
                                <div class="col-12 col-md-6">
                                    <label class="form-label" for="quickPlayer{{ $slot }}">Player {{ $slot }}</label>
                                    <select class="form-select form-select-lg" id="quickPlayer{{ $slot }}" required>
                                        <option value="" selected disabled>Seleccione</option>
                                        @foreach ($players as $player)
                                            <option value="{{ $player->id }}" data-ranking="{{ $rankingByPlayerId[$player->id] ?? '-' }}">{{ $player->nombre }}</option>
                                        @endforeach
                                    </select>
                                    @include('partials.modals.quick-image-picker', ['slot' => $slot])
                                </div>
                            @endforeach
                        </div>

                        <div class="alert alert-info mt-3 mb-3" id="quickSetupMessage">
                            Selecciona el formato y los dos jugadores.
                        </div>

                        <button class="btn btn-success btn-lg w-100" type="button" id="quickStartButton">Iniciar Pleito</button>
                    </div>

                    <input type="hidden" name="ganador_id" id="quickGanadorId">
                    <input type="hidden" name="perdedor_id" id="quickPerdedorId">
                    <input type="hidden" name="ganador_image_id" id="quickGanadorImageId">
                    <input type="hidden" name="perdedor_image_id" id="quickPerdedorImageId">
                    <input type="hidden" name="victorias_ganador" id="quickVictoriasGanador">
                    <input type="hidden" name="victorias_perdedor" id="quickVictoriasPerdedor">

                    <div class="d-none" id="quickCounterStep">
                        <div class="quick-match-title" id="quickMatchIntro">Este match es primero a ganar 5 manos.</div>

                        <div class="quick-match-panel my-3">
                            <div class="quick-match-counter">
                                <div class="quick-player" id="quickPlayerPanel1">
                                    <div class="fw-bold" id="quickPlayerName1">Player 1</div>
                                    <div class="quick-score" id="quickScore1">0</div>
                                    <div class="quick-score-actions">
                                        <button class="btn btn-outline-secondary" type="button" data-quick-player="1" data-quick-action="minus">-</button>
                                        <button class="btn btn-outline-success" type="button" data-quick-player="1" data-quick-action="plus">+</button>
                                    </div>
                                </div>

                                <div class="quick-versus">VS</div>

                                <div class="quick-player" id="quickPlayerPanel2">
                                    <div class="fw-bold" id="quickPlayerName2">Player 2</div>
                                    <div class="quick-score" id="quickScore2">0</div>
                                    <div class="quick-score-actions">
                                        <button class="btn btn-outline-secondary" type="button" data-quick-player="2" data-quick-action="minus">-</button>
                                        <button class="btn btn-outline-success" type="button" data-quick-player="2" data-quick-action="plus">+</button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="alert alert-info mb-3" id="quickMatchMessage">
                            Suma manos hasta que alguien llegue al formato elegido.
                        </div>

                        <div class="quick-share-actions" id="quickShareActions">
                            <button class="btn btn-outline-primary" type="button" id="quickEditButton">Cambiar datos</button>
                            <button class="btn btn-outline-dark" type="button" id="quickPrintButton">PDF / Imprimir</button>
                        </div>
                    </div>
                </div>
                <div class="modal-footer d-none" id="quickMatchFooter">
                    <button class="btn btn-outline-primary" type="button" id="quickShareImageButton">Compartir imagen</button>
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-success btn-lg" id="quickSaveButton" disabled>Guardar Pleito</button>
                </div>
            </form>
        </div>
    </div>
</div>
