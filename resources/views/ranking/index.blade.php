@extends('layouts.app')

@section('title', 'Ranking')
@section('page-title', 'Ranking')

@section('content')
    @include('partials.flash')

    <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
        <h5 class="mb-0">Ranking - Season {{ $season }}</h5>
        <form method="get" action="{{ route('ranking.index') }}">
            <select class="form-select form-select-sm" name="season" aria-label="Temporada" onchange="this.form.submit()">
                @foreach ($availableSeasons as $availableSeason)
                    <option value="{{ $availableSeason }}" @selected((string) $availableSeason === $season)>Season {{ $availableSeason }}</option>
                @endforeach
            </select>
        </form>
    </div>

    <div class="mx-auto" style="max-width: 640px;">
        @forelse ($rankings as $ranking)
            @php
                $percent = $ranking['porcentaje_victoria'];
                $image = $images[$ranking['id']] ?? null;
                $history = $trends[$ranking['id']] ?? [];
                $delta = count($history) >= 2 ? $history[count($history) - 1]['porcentaje'] - $history[count($history) - 2]['porcentaje'] : 0;
                $percentClass = $delta > 0 ? 'text-success' : ($delta < 0 ? 'text-danger' : 'text-body');
            @endphp
            <div class="card mb-3 ranking-card" role="button" tabindex="0" style="cursor: pointer;"
                data-player-id="{{ $ranking['id'] }}" data-player-name="{{ $ranking['nombre'] }}">
                <div class="card-body d-flex align-items-center gap-3 py-3">
                    <div class="display-6 fw-bold text-primary text-center" style="min-width: 2.5rem;">{{ $ranking['ranking_porcentaje'] }}</div>

                    <div class="flex-shrink-0 rounded bg-light" style="width: 72px; height: 72px;">
                        @if ($image)
                            <img src="{{ $image }}" alt="{{ $ranking['nombre'] }}" class="rounded w-100 h-100" style="object-fit: cover;" loading="lazy">
                        @endif
                    </div>

                    <div class="flex-grow-1 text-truncate">
                        <div class="fw-semibold fs-16 text-truncate">{{ $ranking['nombre'] }}</div>
                        <div class="small text-muted">{{ $ranking['total_victorias'] }}V - {{ $ranking['total_derrotas'] }}D &middot; {{ $ranking['total_match'] }} matches</div>
                    </div>

                    <div class="text-end">
                        <div class="fs-3 fw-bold {{ $percentClass }}">{{ number_format($percent, 2) }}%@if ($delta > 0) <small>&#9650;</small>@elseif ($delta < 0) <small>&#9660;</small>@endif</div>
                        <div class="small text-muted">victorias</div>
                    </div>
                </div>
            </div>
        @empty
            <div class="text-center text-muted">No hay ranking para esta temporada.</div>
        @endforelse
    </div>

    <div class="modal fade" id="modalTendencia" tabindex="-1" aria-labelledby="modalTendenciaLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable mx-auto" style="max-width: min(720px, calc(100vw - 1.5rem));">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalTendenciaLabel">Evolucion de victorias</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p class="text-muted small mb-3">% de victorias acumulado de la temporada {{ $season }} al cierre de cada una de las ultimas 5 jornadas.</p>
                    <div style="position: relative; height: clamp(240px, 50vh, 360px);">
                        <canvas id="tendenciaChart"></canvas>
                    </div>
                    <p class="text-center text-muted mt-3 mb-0 d-none" id="tendenciaVacia">Este jugador aun no tiene matches en la temporada.</p>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="{{ asset('velzon/libs/chart.js/chart.umd.min.js') }}"></script>
    <script>
        (function () {
            var trends = @json($trends);
            var modalEl = document.getElementById('modalTendencia');
            var modal = bootstrap.Modal.getOrCreateInstance(modalEl);
            var chart = null;
            var current = null;

            var UP = '#4ade80', DOWN = '#f87171', BASE = '#a5b4fc';

            function pointColors(values) {
                return values.map(function (v, i) {
                    if (i === 0 || v === values[i - 1]) { return BASE; }
                    return v > values[i - 1] ? UP : DOWN;
                });
            }

            var valueLabels = {
                id: 'valueLabels',
                afterDatasetsDraw: function (c) {
                    var ctx = c.ctx;
                    ctx.save();
                    ctx.font = '600 12px sans-serif';
                    ctx.textAlign = 'center';
                    var colors = c.data.datasets[0].labelColors;
                    c.getDatasetMeta(0).data.forEach(function (point, i) {
                        ctx.fillStyle = colors[i] === BASE ? '#6b7280' : (colors[i] === UP ? '#16a34a' : '#dc2626');
                        ctx.fillText(Number(c.data.datasets[0].data[i]).toFixed(2) + '%', point.x, point.y - 14);
                    });
                    ctx.restore();
                }
            };

            function render() {
                var points = trends[current.id] || [];
                document.getElementById('modalTendenciaLabel').textContent = current.name + ' - evolucion de victorias';
                document.getElementById('tendenciaVacia').classList.toggle('d-none', points.length > 0);

                if (chart) { chart.destroy(); }

                var values = points.map(function (p) { return p.porcentaje; });
                var colors = pointColors(values);
                var canvas = document.getElementById('tendenciaChart');
                var gradient = canvas.getContext('2d').createLinearGradient(0, 0, 0, canvas.height);
                gradient.addColorStop(0, 'rgba(165, 180, 252, .55)');
                gradient.addColorStop(1, 'rgba(251, 207, 232, .08)');

                chart = new Chart(canvas, {
                    type: 'line',
                    data: {
                        labels: points.map(function (p) { var d = p.fecha.split('-'); return d[2] + '/' + d[1]; }),
                        datasets: [{
                            data: values,
                            borderColor: '#a5b4fc',
                            backgroundColor: gradient,
                            fill: true,
                            tension: 0.35,
                            borderWidth: 3,
                            pointRadius: 7,
                            pointHoverRadius: 9,
                            pointBackgroundColor: colors,
                            labelColors: colors,
                            pointBorderColor: '#ffffff',
                            pointBorderWidth: 2
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        layout: { padding: { top: 24, right: 16 } },
                        scales: {
                            y: { min: 0, max: 100, ticks: { callback: function (v) { return v + '%'; }, color: '#9ca3af' }, grid: { color: 'rgba(199, 210, 254, .35)' } },
                            x: { ticks: { color: '#9ca3af' }, grid: { display: false } }
                        },
                        plugins: {
                            legend: { display: false },
                            tooltip: {
                                callbacks: {
                                    label: function (item) {
                                        return item.parsed.y.toFixed(2) + '% (' + points[item.dataIndex].total + ' matches)';
                                    }
                                }
                            }
                        }
                    },
                    plugins: [valueLabels]
                });
            }

            modalEl.addEventListener('shown.bs.modal', render);
            modalEl.addEventListener('hidden.bs.modal', function () { if (chart) { chart.destroy(); chart = null; } });

            document.querySelectorAll('.ranking-card').forEach(function (card) {
                function open() {
                    current = { id: card.dataset.playerId, name: card.dataset.playerName };
                    modal.show();
                }
                card.addEventListener('click', open);
                card.addEventListener('keydown', function (e) {
                    if (e.key === 'Enter' || e.key === ' ') { e.preventDefault(); open(); }
                });
            });
        })();
    </script>
@endpush
