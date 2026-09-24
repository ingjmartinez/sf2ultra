<div class="modal fade" id="modalRankingPuntos" tabindex="-1" aria-labelledby="modalRankingPuntosLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-fullscreen-sm-down">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalRankingPuntosLabel">Ranking por puntos - Season {{ $season }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="table-responsive">
                    <table class="table table-sm table-striped">
                        <tr>
                            <th>Ranking</th>
                            <th>Jugador</th>
                            <th>Victorias</th>
                            <th>Derrotas</th>
                            <th>Puntos</th>
                        </tr>
                        @forelse ($rankings as $ranking)
                            <tr>
                                <td>{{ $ranking['ranking'] }}</td>
                                <td>{{ $ranking['nombre'] }}</td>
                                <td>{{ $ranking['victorias'] }}</td>
                                <td>{{ $ranking['derrotas'] }}</td>
                                <td>{{ $ranking['puntos'] }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center text-muted">No hay ranking para esta temporada.</td>
                            </tr>
                        @endforelse
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
