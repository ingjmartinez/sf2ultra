<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdatePlayerRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'nombre' => is_string($this->nombre) ? trim($this->nombre) : $this->nombre,
            'email' => is_string($this->email) ? (trim($this->email) ?: null) : $this->email,
        ]);
    }

    /** @return array<string, list<mixed>> */
    public function rules(): array
    {
        $player = $this->route('player');

        return [
            'nombre' => ['required', 'string', 'max:100', Rule::unique('players', 'nombre')->ignore($this->route('player'))],
            'email' => [
                'nullable', 'email', 'max:255', 'required_with:password',
                Rule::requiredIf(fn () => (bool) $player?->user_id),
                Rule::when($this->filled('password') || $player?->user_id, [Rule::unique('users', 'email')->ignore($player?->user_id)]),
            ],
            'password' => ['nullable', 'string', 'min:8', 'max:72'],
            'image_id' => ['nullable', 'integer', 'exists:images,id'],
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'nombre.required' => 'El nombre es obligatorio.',
            'nombre.max' => 'El nombre no puede superar los 100 caracteres.',
            'nombre.unique' => 'Ya existe un jugador con ese nombre.',
            'email.email' => 'El correo no es valido.',
            'email.required' => 'El jugador tiene acceso: el correo es obligatorio.',
            'email.required_with' => 'Para crear una contrasena el jugador necesita un correo.',
            'email.unique' => 'Ese correo ya tiene una cuenta.',
            'password.min' => 'La contrasena debe tener al menos 8 caracteres.',
            'password.max' => 'La contrasena no puede superar los 72 caracteres.',
        ];
    }
}
