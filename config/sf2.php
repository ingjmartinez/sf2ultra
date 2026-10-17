<?php

return [

    /*
    | Hora (0-23) hasta la cual un pleito se considera de madrugada. Al iniciar o
    | cambiar la fecha de un pleito en ese horario se muestra un aviso para validar
    | la hora (no se bloquea el registro).
    */
    'match_cutoff_hour' => (int) env('MATCH_CUTOFF_HOUR', 6),

];
