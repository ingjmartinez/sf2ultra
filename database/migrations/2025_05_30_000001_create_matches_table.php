<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('matches')) {
            return;
        }

        Schema::create('matches', function (Blueprint $table) {
            $table->id();
            $table->dateTime('fecha');
            $table->foreignId('ganador_id')->nullable()->constrained('players');
            $table->foreignId('perdedor_id')->nullable()->constrained('players');
            $table->unsignedTinyInteger('victorias_ganador')->nullable();
            $table->unsignedTinyInteger('victorias_perdedor')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('matches');
    }
};
