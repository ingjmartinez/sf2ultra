<div class="modal fade" id="modalAvisoHora" tabindex="-1" aria-labelledby="modalAvisoHoraLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalAvisoHoraLabel"><i class="ri-time-line align-bottom me-1 text-warning"></i> Valida la hora del registro</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p class="mb-2">La hora indicada es de madrugada (<strong id="avisoHoraValor"></strong>), es decir, ya pasaron las 11:59 pm.</p>
                <p class="mb-0 text-muted">Si este pleito se jugo el dia anterior, corrige la fecha. De lo contrario puedes continuar.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Corregir</button>
                <button type="button" class="btn btn-warning" id="avisoHoraContinuar">Continuar</button>
            </div>
        </div>
    </div>
</div>
