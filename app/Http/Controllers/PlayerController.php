<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePlayerRequest;
use App\Http\Requests\UpdatePlayerRequest;
use App\Models\Image;
use App\Models\Player;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
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
        DB::transaction(function () use ($request) {
            $player = Player::create(Arr::except($request->validated(), 'password'));

            $this->syncAccount($player, $request->validated('password'));
        });

        return redirect()->back()->with('success', 'Player registrado correctamente.');
    }

    public function update(UpdatePlayerRequest $request, Player $player): RedirectResponse
    {
        DB::transaction(function () use ($request, $player) {
            $player->update(Arr::except($request->validated(), 'password'));

            $this->syncAccount($player, $request->validated('password'));
        });

        return redirect()->route('players.index')->with('success', 'Player actualizado correctamente.');
    }

    /** Crea o actualiza la cuenta de acceso del player (la contrasena solo se cambia si se envia). */
    private function syncAccount(Player $player, ?string $password): void
    {
        if ($player->user_id) {
            $user = $player->user;
            $user->name = $player->nombre;
            $user->email = $player->email ?? $user->email;

            if ($password) {
                $user->password = $password;
            }

            $user->save();

            return;
        }

        if ($password) {
            $user = User::create([
                'name' => $player->nombre,
                'email' => $player->email,
                'password' => $password,
                'is_admin' => false,
            ]);

            $player->update(['user_id' => $user->id]);
        }
    }
}
