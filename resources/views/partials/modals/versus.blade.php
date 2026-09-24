<div class="modal fade" id="modalVSDia" tabindex="-1" aria-labelledby="modalVSDiaLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-fullscreen-sm-down">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalVSDiaLabel">&#9876; VS del Dia</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">

                <div class="row g-2 align-items-end mb-4">
                    <div class="col-12 col-md-6 col-lg-3">
                        <label class="form-label" for="vsFecha">Fecha</label>
                        <input class="form-control form-control-lg" id="vsFecha" type="date" value="{{ $currentDate }}" required>
                    </div>
                    <div class="col-12 col-md-6 col-lg-4">
                        <label class="form-label" for="vsPlayer1Sel">Player 1</label>
                        <select class="form-select form-select-lg" id="vsPlayer1Sel" required>
                            <option value="" selected disabled>Seleccione</option>
                            @foreach ($players as $player)
                                <option value="{{ $player->id }}">{{ $player->nombre }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-12 col-md-6 col-lg-4">
                        <label class="form-label" for="vsPlayer2Sel">Player 2</label>
                        <select class="form-select form-select-lg" id="vsPlayer2Sel" required>
                            <option value="" selected disabled>Seleccione</option>
                            @foreach ($players as $player)
                                <option value="{{ $player->id }}">{{ $player->nombre }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-12 col-lg-1">
                        <button class="btn btn-danger btn-lg w-100" id="vsBuscarBtn" type="button">Ver</button>
                    </div>
                </div>

                <div id="vsError" class="alert alert-danger d-none"></div>

                <div id="vsResultados" class="d-none">
                    <h4 id="vsTitulo" class="text-center mb-4 fw-bold"></h4>

                    <div class="row align-items-center mb-4">
                        @foreach ([1, 2] as $slot)
                            @if ($slot === 2)
                                <div class="col-2 text-center vs-divider">
                                    <span class="display-4 fw-bold text-secondary">VS</span>
                                </div>
                            @endif
                            <div class="col-5">
                                <div class="card text-center shadow border-2" id="vsCard{{ $slot }}">
                                    <div class="card-header fw-bold fs-5" id="vsNombre{{ $slot }}"></div>
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-6">
                                                <div class="text-muted small">V</div>
                                                <div class="fs-2 fw-bold text-success" id="vsV{{ $slot }}">0</div>
                                            </div>
                                            <div class="col-6">
                                                <div class="text-muted small">D</div>
                                                <div class="fs-2 fw-bold text-danger" id="vsD{{ $slot }}">0</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <div class="mb-4">
                        <h5>Pleitos directos ese dia</h5>
                        <div class="table-responsive">
                            <table class="table table-sm table-striped align-middle" id="vsH2HTable">
                                <thead>
                                    <tr>
                                        <th>Hora</th>
                                        <th>Ganador</th>
                                        <th>Perdedor</th>
                                        <th>Resultado</th>
                                    </tr>
                                </thead>
                                <tbody></tbody>
                            </table>
                        </div>
                    </div>

                    <div class="row">
                        @foreach ([1, 2] as $slot)
                            <div class="col-12 col-md-6 mb-4">
                                <h5 id="vsMatchesTitle{{ $slot }}"></h5>
                                <div class="table-responsive">
                                    <table class="table table-sm align-middle" id="vsMatches{{ $slot }}Table">
                                        <thead>
                                            <tr>
                                                <th>Hora</th>
                                                <th>Rival</th>
                                                <th>V/D</th>
                                                <th>Resultado</th>
                                            </tr>
                                        </thead>
                                        <tbody></tbody>
                                    </table>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-success" id="vsShareButton">Compartir</button>
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>
