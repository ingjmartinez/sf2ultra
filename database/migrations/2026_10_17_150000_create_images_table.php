<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('images', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 100)->nullable();
            $table->string('ruta');
            $table->timestamps();
        });

        Schema::table('players', function (Blueprint $table) {
            $table->foreignId('image_id')->nullable()->after('email')->constrained('images')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('players', function (Blueprint $table) {
            $table->dropConstrainedForeignId('image_id');
        });

        Schema::dropIfExists('images');
    }
};
