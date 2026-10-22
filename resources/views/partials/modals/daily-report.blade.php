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
                    <p class="text-muted small mb-2">Orden: victorias, enfrentamientos directos y win rate del dia.</p>
                    <div class="table-responsive">
                        <table class="table table-sm table-striped align-middle daily-ranking-table" id="dailyRankingTable">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Jugador</th>
                                    <th>V</th>
                                    <th>D</th>
                                    <th>Win rate</th>
                                    <th>Puntos</th>
                                    <th>Manos</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($dailyRankings as $row)
                                    <tr @class(['daily-ranking-leader' => $loop->first, 'daily-ranking-runner-up' => $loop->iteration === 2, 'daily-ranking-third' => $loop->iteration === 3])>
                                        <td>{{ $loop->iteration }}</td>
                                        <td>
                                            @if ($loop->iteration <= 3 && ($dailyTopImages[$row['id']] ?? null))
                                                <img src="{{ $dailyTopImages[$row['id']] }}" alt="Personaje de {{ $row['nombre'] }}" class="daily-ranking-character" loading="lazy">
                                            @endif
                                            <span class="daily-ranking-name">{{ $row['nombre'] }}</span>
                                        </td>
                                        <td>{{ $row['victorias'] }}</td>
                                        <td>{{ $row['derrotas'] }}</td>
                                        <td>{{ number_format($row['win_rate'], 2) }}%</td>
                                        <td>{{ $row['puntos'] }}</td>
                                        <td>{{ $row['manos_favor'] }} - {{ $row['manos_contra'] }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center text-muted">No hay ranking para este dia.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <div>
                    <h5>Pleitos del dia</h5>
                    <p class="small text-muted mb-2">Cambio en porcentaje de victoria: +22 equivale a +0.22 puntos porcentuales.</p>
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
                                        <td>
                                            @if ($dailyMatch->ganadorImage)
                                                <img src="{{ $dailyMatch->ganadorImage->url }}" alt="{{ $dailyMatch->ganadorImage->nombre }}" class="rounded-circle me-1" style="width: 28px; height: 28px; object-fit: cover;" loading="lazy">
                                            @endif
                                            <span class="daily-player-name">{{ $dailyMatch->ganador->nombre }}</span>
                                            <span class="daily-win-rate-change text-success fw-bold" data-win-rate-change title="Cambio en porcentaje de victoria: +{{ number_format($dailyWinRateChanges[$dailyMatch->id]['winner'], 2) }} pp">
                                                +{{ sprintf('%02d', (int) round($dailyWinRateChanges[$dailyMatch->id]['winner'] * 100)) }}
                                            </span>
                                        </td>
                                        <td>
                                            @if ($dailyMatch->perdedorImage)
                                                <img src="{{ $dailyMatch->perdedorImage->url }}" alt="{{ $dailyMatch->perdedorImage->nombre }}" class="rounded-circle me-1" style="width: 28px; height: 28px; object-fit: cover;" loading="lazy">
                                            @endif
                                            <span class="daily-player-name">{{ $dailyMatch->perdedor->nombre }}</span>
                                            <span class="daily-win-rate-change text-danger fw-bold" data-win-rate-change title="Cambio en porcentaje de victoria: -{{ number_format(abs($dailyWinRateChanges[$dailyMatch->id]['loser']), 2) }} pp">
                                                -{{ sprintf('%02d', (int) round(abs($dailyWinRateChanges[$dailyMatch->id]['loser']) * 100)) }}
                                            </span>
                                        </td>
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
