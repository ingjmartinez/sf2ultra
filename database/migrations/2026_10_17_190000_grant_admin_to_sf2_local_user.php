<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('users')->where('email', 'admin@sf2.local')->update(['is_admin' => true]);
    }

    public function down(): void
    {
        // No se revoca el permiso de administrador.
    }
};
