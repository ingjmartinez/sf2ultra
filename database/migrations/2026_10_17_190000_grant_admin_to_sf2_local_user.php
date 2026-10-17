<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Autorreparacion: si la migracion de cuentas de players no dejo sus columnas
        // (p. ej. en un entorno donde quedo registrada sin aplicarse), se crean aqui.
        if (! Schema::hasColumn('users', 'is_admin')) {
            Schema::table('users', function (Blueprint $table) {
                $table->boolean('is_admin')->default(false)->after('password');
            });

            // Hasta ahora todos los usuarios eran administradores.
            DB::table('users')->update(['is_admin' => true]);
        }

        if (! Schema::hasColumn('players', 'user_id')) {
            Schema::table('players', function (Blueprint $table) {
                $table->foreignId('user_id')->nullable()->after('image_id')->constrained('users')->nullOnDelete();
            });
        }

        DB::table('users')->where('email', 'admin@sf2.local')->update(['is_admin' => true]);
    }

    public function down(): void
    {
        // No se revoca el permiso de administrador.
    }
};
