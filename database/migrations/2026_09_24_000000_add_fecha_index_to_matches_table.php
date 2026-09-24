<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasIndex('matches', 'matches_fecha_index')) {
            return;
        }

        Schema::table('matches', function (Blueprint $table) {
            $table->index('fecha', 'matches_fecha_index');
        });
    }

    public function down(): void
    {
        if (Schema::hasIndex('matches', 'matches_fecha_index')) {
            Schema::table('matches', function (Blueprint $table) {
                $table->dropIndex('matches_fecha_index');
            });
        }
    }
};
