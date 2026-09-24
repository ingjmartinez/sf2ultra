@extends('layouts.app')

@section('title', 'Registro de Matches')

@section('content')
    @include('partials.flash')

    @include('partials.modals.season')
    @include('partials.modals.daily-report')
    @include('partials.modals.versus')
    @include('partials.modals.quick-match')
    @include('partials.modals.edit-match')
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

    <div class="matches-toolbar">
        <div class="text-muted small" id="matchesPaginationInfo"></div>
        <div class="text-muted small">10 registros por pagina</div>
    </div>

    <div class="table-responsive">
        <table class="table table-sm table-striped align-middle" id="matchesTable">
            <thead>
                <tr>
                    <th>Fecha</th>
                    <th>Ganador</th>
                    <th>Perdedor</th>
                    <th>Puntos Victorias</th>
                    <th>Puntos Derrotas</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($matches as $match)
                    <tr class="match-row">
                        <td>{{ $match->fecha->format('Y-m-d H:i:s') }}</td>
                        <td>{{ $match->ganador->nombre }}</td>
                        <td>{{ $match->perdedor->nombre }}</td>
                        <td>{{ $match->victorias_ganador }}</td>
                        <td>{{ $match->victorias_perdedor }}</td>
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
                            
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <div class="matches-pagination" id="matchesPagination"></div>
@endsection

@push('scripts')
    <script>
        window.SF2 = {{ Illuminate\Support\Js::from([
            'season' => $season,
            'openDailyReport' => $openDailyReport,
            'routes' => [
                'playerStats' => route('api.player-stats'),
                'versus' => route('api.versus'),
            ],
        ]) }};
    </script>
    <script src="{{ asset('js/dashboard.js') }}"></script>
@endpush
