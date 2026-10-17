<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreImageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, list<string>> */
    public function rules(): array
    {
        return [
            'nombre' => ['nullable', 'string', 'max:100'],
            'imagen' => ['required', 'image', 'mimes:jpg,jpeg,png,webp,gif', 'max:2048'],
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'imagen.required' => 'Selecciona una imagen.',
            'imagen.image' => 'El archivo debe ser una imagen.',
            'imagen.mimes' => 'Formatos permitidos: jpg, png, webp o gif.',
            'imagen.max' => 'La imagen no puede superar los 2 MB.',
            'imagen.uploaded' => 'No se pudo subir la imagen (maximo 2 MB).',
        ];
    }
}
