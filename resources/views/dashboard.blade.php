@extends('layouts.app')

@section('title', 'Registro de Matches')

@section('content')
    @include('partials.flash')

    @include('partials.modals.season')
    @include('partials.modals.daily-report')
    @include('partials.modals.versus')
    @include('partials.modals.quick-match')
    @include('partials.modals.edit-match')
    @include('partials.modals.hour-warning')
    @include('partials.modals.player')
    @include('partials.modals.player-stats')
    @include('partials.modals.ranking')

    <h2>Estadisticas - Season {{ $season }}</h2>
    <div class="table-responsive">
        <table class="table table-sm table-striped">
            <tr>
                <th>Ranking Porcentaje</th>
                <th>Jugador</th>
                <th>Total Match</th>
                <th>Total Victorias</th>
                <th>Total Derrotas</th>
                <th>Porcentaje Victoria</th>
                <th>Porcentaje Derrota</th>
                <th>Accion</th>
            </tr>
            @forelse ($seasonStats as $stats)
                <tr>
                    <td>{{ $stats['ranking_porcentaje'] }}</td>
                    <td>{{ $stats['nombre'] }}</td>
                    <td>{{ $stats['total_match'] }}</td>
                    <td>{{ $stats['total_victorias'] }}</td>
                    <td>{{ $stats['total_derrotas'] }}</td>
                    <td>
                        {{ number_format($stats['porcentaje_victoria'], 2) }}%
                        @if ($stats['tendencia'] === 'up')
                            <span class="text-success fw-bold ms-1" title="Subiendo">&#9650;</span>
                        @else
                            <span class="text-danger fw-bold ms-1" title="Bajando">&#9660;</span>
                        @endif
                    </td>
                    <td>{{ number_format($stats['porcentaje_derrota'], 2) }}%</td>
                    <td><button type="button" class="btn btn-sm btn-primary btn-ver-estadisticas" data-player-id="{{ $stats['id'] }}">Ver</button></td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" class="text-center text-muted">No hay estadisticas para esta temporada.</td>
                </tr>
            @endforelse
        </table>
    </div>

    <h2>Matches Points - Season {{ $season }}</h2>
    <p class="small text-muted">Cambio en porcentaje de victoria: +22 equivale a +0.22 puntos porcentuales.</p>
    <form action="{{ route('dashboard') }}" method="get">
        <input type="hidden" name="season" value="{{ $season }}">
        <div class="matches-filter">
            <div>
                <label class="form-label" for="playerFiltro">Player</label>
                <select class="form-select form-select-sm" id="playerFiltro" name="player_filtro">
                    <option value="" @selected($filterPlayer === 0) disabled>Seleccione</option>
                    @foreach ($players as $player)
                        <option value="{{ $player->id }}" @selected($player->id === $filterPlayer)>{{ $player->nombre }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="form-label" for="fechaFiltro">Fecha</label>
                <input class="form-control form-control-sm" id="fechaFiltro" type="date" name="fecha_filtro" value="{{ $filterDate }}">
            </div>
            <div>
                <button class="btn btn-info btn-sm" type="submit">Filtrar</button>
            </div>
            <div>
                <a href="{{ route('dashboard', ['season' => $season]) }}" class="btn btn-outline-info btn-sm">Limpiar</a>
            </div>
        </div>
    </form>

    <div class="table-responsive">
        <table class="table table-sm table-striped align-middle w-100" id="matchesTable">
            <thead>
                <tr>
                    <th>Fecha</th>
                    <th>Ganador</th>
                    <th>Perdedor</th>
                    <th>Puntos Victorias</th>
                    <th>Puntos Derrotas</th>
                    <th>Registrado por</th>
                    @if (auth()->user()->is_admin)
                        <th class="no-sort">Acciones</th>
                    @endif
                </tr>
            </thead>
            <tbody>
                @foreach ($matches as $match)
                    <tr class="match-row">
                        <td data-order="{{ $match->id }}">{{ $match->fecha->format('Y-m-d H:i:s') }}</td>
                        <td>
                            @if ($match->ganadorImage)
                                <img src="{{ $match->ganadorImage->url }}" alt="{{ $match->ganadorImage->nombre }}" title="{{ $match->ganadorImage->nombre }}" class="rounded-circle me-1" style="width: 24px; height: 24px; object-fit: cover;" loading="lazy">
                            @endif
                            {{ $match->ganador->nombre }}
                            <span class="daily-win-rate-change text-success fw-bold" title="Cambio en porcentaje de victoria: +{{ number_format($matchWinRateChanges[$match->id]['winner'], 2) }} pp">+{{ sprintf('%02d', (int) round($matchWinRateChanges[$match->id]['winner'] * 100)) }}</span>
                        </td>
                        <td>
                            @if ($match->perdedorImage)
                                <img src="{{ $match->perdedorImage->url }}" alt="{{ $match->perdedorImage->nombre }}" title="{{ $match->perdedorImage->nombre }}" class="rounded-circle me-1" style="width: 24px; height: 24px; object-fit: cover;" loading="lazy">
                            @endif
                            {{ $match->perdedor->nombre }}
                            <span class="daily-win-rate-change text-danger fw-bold" title="Cambio en porcentaje de victoria: -{{ number_format(abs($matchWinRateChanges[$match->id]['loser']), 2) }} pp">-{{ sprintf('%02d', (int) round(abs($matchWinRateChanges[$match->id]['loser']) * 100)) }}</span>
                        </td>
                        <td>{{ $match->victorias_ganador }}</td>
                        <td>{{ $match->victorias_perdedor }}</td>
                        <td>{{ $match->registradoPor?->name ?? '—' }}</td>
                        @if (auth()->user()->is_admin)
                        <td>
                            <button
                                type="button"
                                class="btn btn-sm btn-outline-primary edit-match-button"
                                data-bs-toggle="modal"
                                data-bs-target="#modalEditarMatch"
                                data-update-url="{{ route('matches.update', $match) }}"
                                data-fecha="{{ $match->fecha->format('Y-m-d H:i:s') }}"
                                data-ganador="{{ $match->ganador->nombre }}"
                                data-perdedor="{{ $match->perdedor->nombre }}"
                                data-ganador-id="{{ $match->ganador_id }}"
                                data-perdedor-id="{{ $match->perdedor_id }}"
                                data-victorias-ganador="{{ $match->victorias_ganador }}"
                                data-victorias-perdedor="{{ $match->victorias_perdedor }}"
                            >Editar</button>
                            <form method="post" action="{{ route('matches.destroy', $match) }}" class="d-inline form-eliminar-match"
                                data-match="{{ $match->ganador->nombre }} vs {{ $match->perdedor->nombre }} ({{ $match->victorias_ganador }}-{{ $match->victorias_perdedor }})">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger">Eliminar</button>
                            </form>
                        </td>
                        @endif
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endsection

@push('styles')
    <link href="{{ asset('velzon/libs/datatables/dataTables.bootstrap5.min.css') }}" rel="stylesheet">
@endpush

@push('scripts')
    <script src="{{ asset('velzon/libs/sweetalert2/sweetalert2.all.min.js') }}"></script>
    <script src="{{ asset('velzon/libs/datatables/jquery.min.js') }}"></script>
    <script src="{{ asset('velzon/libs/datatables/dataTables.min.js') }}"></script>
    <script src="{{ asset('velzon/libs/datatables/dataTables.bootstrap5.min.js') }}"></script>
    <script>
        // Las filas se redibujan al paginar, por eso se delega el evento en la tabla.
        document.getElementById('matchesTable').addEventListener('submit', function (event) {
            var form = event.target.closest('.form-eliminar-match');

            if (!form) { return; }

            event.preventDefault();

            Swal.fire({
                title: '¿Deseas eliminar este pleito?',
                html: '<strong>' + form.dataset.match.replace(/&/g, '&amp;').replace(/</g, '&lt;') + '</strong><br>Se restaran sus victorias, derrotas y puntos. Esta accion no se puede deshacer.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Si, eliminar',
                cancelButtonText: 'No, cancelar',
                confirmButtonColor: '#f06548',
                cancelButtonColor: '#6c757d',
                reverseButtons: true,
                focusCancel: true
            }).then(function (result) {
                if (result.isConfirmed) {
                    form.submit();
                }
            });
        });

        new DataTable('#matchesTable', {
            order: [[0, 'desc']],
            pageLength: 10,
            lengthMenu: [10, 25, 50, 100],
            columnDefs: [{ orderable: false, searchable: false, targets: 'no-sort' }],
            language: {
                search: 'Buscar:',
                lengthMenu: 'Mostrar _MENU_ registros',
                info: 'Mostrando _START_-_END_ de _TOTAL_ matches',
                infoEmpty: 'Sin matches registrados',
                infoFiltered: '(filtrado de _MAX_)',
                zeroRecords: 'No se encontraron matches',
                emptyTable: 'Sin matches registrados',
                paginate: { first: '«', previous: '‹', next: '›', last: '»' }
            }
        });
    </script>
    <script>
        window.SF2 = {{ Illuminate\Support\Js::from([
            'season' => $season,
            'openDailyReport' => $openDailyReport,
            'matchCutoffHour' => config('sf2.match_cutoff_hour'),
            'routes' => [
                'playerStats' => route('api.player-stats'),
                'versus' => route('api.versus'),
            ],
        ]) }};
    </script>
    <script src="{{ asset('js/dashboard.js') }}?v={{ filemtime(public_path('js/dashboard.js')) }}"></script>
    <script>
        (function () {
            var id = new URLSearchParams(window.location.search).get('abrir');
            var modal = id && /^modal\w+$/.test(id) ? document.getElementById(id) : null;

            if (modal) {
                bootstrap.Modal.getOrCreateInstance(modal).show();
            }
        })();
    </script>
@endpush
