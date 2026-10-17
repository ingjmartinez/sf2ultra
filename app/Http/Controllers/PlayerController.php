<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePlayerRequest;
use App\Http\Requests\UpdatePlayerRequest;
use App\Models\Image;
use App\Models\Player;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class PlayerController extends Controller
{
    public function index(): View
    {
        return view('players.index', [
            'players' => Player::query()->with('image')->orderBy('nombre')->get(),
            'images' => Image::query()->orderBy('nombre')->get(),
        ]);
    }

    public function store(StorePlayerRequest $request): RedirectResponse
    {
        Player::create($request->validated());

        return redirect()->back()->with('success', 'Player registrado correctamente.');
    }

    public function update(UpdatePlayerRequest $request, Player $player): RedirectResponse
    {
        $player->update($request->validated());

        return redirect()->route('players.index')->with('success', 'Player actualizado correctamente.');
    }
}
