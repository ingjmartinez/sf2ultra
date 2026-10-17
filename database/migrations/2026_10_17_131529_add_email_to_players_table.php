<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('players', 'email')) {
            return;
        }

        Schema::table('players', function (Blueprint $table) {
            $table->string('email')->nullable()->after('nombre');
        });
    }

    public function down(): void
    {
        if (Schema::hasColumn('players', 'email')) {
            Schema::table('players', function (Blueprint $table) {
                $table->dropColumn('email');
            });
        }
    }
};
