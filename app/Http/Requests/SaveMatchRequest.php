<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SaveMatchRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, list<string>> */
    public function rules(): array
    {
        return [
            'fecha' => ['required', 'date'],
            'ganador_id' => ['required', 'integer', 'exists:players,id'],
            'perdedor_id' => ['required', 'integer', 'exists:players,id', 'different:ganador_id'],
            'victorias_ganador' => ['required', 'integer', 'in:3,5'],
            'victorias_perdedor' => ['required', 'integer', 'min:0', 'lt:victorias_ganador'],
            'season' => ['nullable', 'digits:4'],
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'fecha.required' => 'Debes indicar la fecha del match.',
            'fecha.date' => 'La fecha del match no es valida.',
            'ganador_id.required' => 'Debes seleccionar el ganador.',
            'ganador_id.exists' => 'El ganador seleccionado no existe.',
            'perdedor_id.required' => 'Debes seleccionar el perdedor.',
            'perdedor_id.exists' => 'El perdedor seleccionado no existe.',
            'perdedor_id.different' => 'Ganador y Perdedor no pueden ser el mismo jugador.',
            'victorias_ganador.in' => 'El combate debe ser a ganar 3 o 5 manos.',
            'victorias_perdedor.required' => 'Debes indicar las manos del perdedor.',
            'victorias_perdedor.min' => 'Las manos del perdedor no pueden ser negativas.',
            'victorias_perdedor.lt' => 'El ganador debe tener mas manos que el perdedor.',
        ];
    }

    /** @return array<string, mixed> */
    public function matchData(): array
    {
        return $this->safe()->only([
            'fecha',
            'ganador_id',
            'perdedor_id',
            'victorias_ganador',
            'victorias_perdedor',
        ]);
    }
}
