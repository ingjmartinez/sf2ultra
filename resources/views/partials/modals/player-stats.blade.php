<div class="modal fade" id="modalEstadisticas" tabindex="-1" aria-labelledby="modalEstadisticasLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-fullscreen-lg-down">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalEstadisticasLabel">Estadisticas - Season {{ $season }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">

                <div class="row">
                    <div class="col-12 col-xl-2 mb-3">
                        <div class="card text-center shadow-sm">
                            <div class="card-body p-2">
                                <h4 class="card-title text-muted">Seleccionar Player:</h4>
                                <select id="cboPlayerEstadistica" class="form-select form-select-lg" required>
                                    <option value="" selected disabled>Seleccione</option>
                                    @foreach ($players as $player)
                                        <option value="{{ $player->id }}">{{ $player->nombre }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="col-12 col-xl-10 mb-3">
                        <div class="row">
                            <div class="col-6 col-md-4 col-xl-2 mb-3">
                                <div class="card text-center shadow-sm">
                                    <div class="card-body p-2">
                                        <h4 class="card-title text-muted">Ranking Porcentaje</h4>
                                        <h2 class="card-text" id="ranking">-</h2>
                                    </div>
                                </div>
                            </div>

                            <div class="col-6 col-md-4 col-xl-2 mb-3">
                                <div class="card text-center shadow-sm">
                                    <div class="card-body p-2">
                                        <h4 class="card-title text-muted">Total de Match</h4>
                                        <h2 class="card-text" id="totalMatch">0</h2>
                                    </div>
                                </div>
                            </div>

                            <div class="col-6 col-md-4 col-xl-2 mb-3">
                                <div class="card text-center shadow-sm">
                                    <div class="card-body p-2">
                                        <h4 class="card-title text-muted">Total de Victorias</h4>
                                        <h2 class="card-text" id="totalVictorias">0</h2>
                                    </div>
                                </div>
                            </div>

                            <div class="col-6 col-md-4 col-xl-2 mb-3">
                                <div class="card text-center shadow-sm">
                                    <div class="card-body p-2">
                                        <h4 class="card-title text-muted">Total de Derrotas</h4>
                                        <h2 class="card-text" id="totalDerrotas">0</h2>
                                    </div>
                                </div>
                            </div>

                            <div class="col-6 col-md-4 col-xl-2 mb-3">
                                <div class="card text-center shadow-sm">
                                    <div class="card-body p-2">
                                        <h4 class="card-title text-muted">Porcentaje de Victoria</h4>
                                        <h2 class="card-text">
                                            <span id="porcentajeVictoria">0</span>%
                                        </h2>
                                    </div>
                                </div>
                            </div>

                            <div class="col-6 col-md-4 col-xl-2 mb-3">
                                <div class="card text-center shadow-sm">
                                    <div class="card-body p-2">
                                        <h4 class="card-title text-muted">Porcentaje de Derrota</h4>
                                        <h2 class="card-text">
                                            <span id="porcentajeDerrota">0</span>%
                                        </h2>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="table-responsive mt-4">
                    <table id="tableEstadisticas" class="table table-sm">
                        <thead>
                            <tr>
                                <th>Player 1</th>
                                <th>VS</th>
                                <th>Player 2</th>
                                <th>Total Match</th>
                                <th>Victorias</th>
                                <th>Derrotas</th>
                                <th>Porcentaje Victoria</th>
                                <th>Porcentaje Derrota</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
