<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('matches', function (Blueprint $table) {
            $table->foreignId('ganador_image_id')->nullable()->after('user_id')->constrained('images')->nullOnDelete();
            $table->foreignId('perdedor_image_id')->nullable()->after('ganador_image_id')->constrained('images')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('matches', function (Blueprint $table) {
            $table->dropConstrainedForeignId('perdedor_image_id');
            $table->dropConstrainedForeignId('ganador_image_id');
        });
    }
};
