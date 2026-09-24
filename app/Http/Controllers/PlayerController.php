<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePlayerRequest;
use App\Models\Player;
use Illuminate\Http\RedirectResponse;

class PlayerController extends Controller
{
    public function store(StorePlayerRequest $request): RedirectResponse
    {
        Player::create($request->validated());

        return redirect()->back()->with('success', 'Player registrado correctamente.');
    }
}
