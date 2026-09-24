<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('players')) {
            return;
        }

        Schema::create('players', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 100);
            $table->integer('victorias')->default(0);
            $table->integer('derrotas')->default(0);
            $table->integer('puntos')->default(0);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('players');
    }
};
