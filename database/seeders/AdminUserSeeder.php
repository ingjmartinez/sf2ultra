<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        $email = env('ADMIN_EMAIL');
        $password = env('ADMIN_PASSWORD');

        if (! $email || ! $password) {
            $this->command?->warn('ADMIN_EMAIL y ADMIN_PASSWORD no estan definidos en .env; no se creo el administrador.');

            return;
        }

        User::updateOrCreate(
            ['email' => $email],
            ['name' => 'Administrador', 'password' => $password, 'is_admin' => true],
        );
    }
}
