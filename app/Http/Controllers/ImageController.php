<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreImageRequest;
use App\Models\Image;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ImageController extends Controller
{
    public function index(): View
    {
        return view('images.index', [
            'images' => Image::query()->withCount('players')->latest('id')->get(),
        ]);
    }

    public function store(StoreImageRequest $request): RedirectResponse
    {
        $file = $request->file('imagen');

        Image::create([
            'nombre' => $request->validated('nombre') ?: pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME),
            'ruta' => $file->store('imagenes', 'uploads'),
        ]);

        return redirect()->route('imagenes.index')->with('success', 'Imagen cargada correctamente.');
    }

    public function destroy(Image $image): RedirectResponse
    {
        $image->delete();

        return redirect()->route('imagenes.index')->with('success', 'Imagen eliminada.');
    }
}
