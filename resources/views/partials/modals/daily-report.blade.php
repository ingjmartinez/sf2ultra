<div class="modal fade" id="modalReporteDia" tabindex="-1" aria-labelledby="modalReporteDiaLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-fullscreen-sm-down">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalReporteDiaLabel">Reporte del Dia</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form class="row g-2 align-items-end mb-3" method="get" action="{{ route('dashboard') }}">
                    <input type="hidden" name="season" value="{{ $season }}">
                    <div class="col-12 col-sm-8">
                        <label class="form-label" for="reportFecha">Fecha</label>
                        <input class="form-control form-control-lg" id="reportFecha" type="date" name="report_fecha" value="{{ $reportDate }}" required>
                    </div>
                    <div class="col-12 col-sm-4">
                        <button class="btn btn-dark btn-lg w-100" type="submit">Ver Dia</button>
                    </div>
                </form>

                <div class="text-center mb-3">
                    <h4 class="mb-1">Reporte del Dia</h4>
                    <div class="text-muted fw-bold">{{ $reportDate }}</div>
                </div>

                <div class="mb-4">
                    <h5>Mini ranking del dia</h5>
                    <div class="table-responsive">
                        <table class="table table-sm table-striped align-middle" id="dailyRankingTable">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Jugador</th>
                                    <th>V</th>
                                    <th>D</th>
                                    <th>Puntos</th>
                                    <th>Manos</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($dailyRankings as $row)
                                    <tr>
                                        <td>{{ $loop->iteration }}</td>
                                        <td>{{ $row['nombre'] }}</td>
                                        <td>{{ $row['victorias'] }}</td>
                                        <td>{{ $row['derrotas'] }}</td>
                                        <td>{{ $row['puntos'] }}</td>
                                        <td>{{ $row['manos_favor'] }} - {{ $row['manos_contra'] }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center text-muted">No hay ranking para este dia.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <div>
                    <h5>Pleitos del dia</h5>
                    <div class="table-responsive daily-matches-scroll">
                        <table class="table table-sm table-striped align-middle" id="dailyMatchesTable">
                            <thead>
                                <tr>
                                    <th>Fecha</th>
                                    <th>Ganador</th>
                                    <th>Perdedor</th>
                                    <th>Resultado</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($dailyMatches as $dailyMatch)
                                    <tr>
                                        <td>{{ $dailyMatch->fecha->format('Y-m-d H:i:s') }}</td>
                                        <td>{{ $dailyMatch->ganador->nombre }}</td>
                                        <td>{{ $dailyMatch->perdedor->nombre }}</td>
                                        <td>{{ $dailyMatch->victorias_ganador }} - {{ $dailyMatch->victorias_perdedor }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center text-muted">No hay pleitos registrados para este dia.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <div class="daily-report-actions">
                    <button type="button" class="btn btn-outline-success" id="dailyReportShareButton">Compartir</button>
                    <button type="button" class="btn btn-outline-primary" id="dailyReportImageButton">Exportar JPG</button>
                    <button type="button" class="btn btn-outline-dark" id="dailyReportPrintButton">PDF / Imprimir</button>
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cerrar</button>
                </div>
            </div>
        </div>
    </div>
</div>
