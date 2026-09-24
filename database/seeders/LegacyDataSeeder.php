<?php

namespace Database\Seeders;

use App\Services\MatchService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class LegacyDataSeeder extends Seeder
{
    public function run(): void
    {
        if (DB::table('players')->exists() || DB::table('matches')->exists()) {
            $this->command?->warn('Las tablas ya contienen datos; se omite la importacion historica.');

            return;
        }

        $players = json_decode(file_get_contents(__DIR__.'/data/players.json'), true);
        $matches = json_decode(file_get_contents(__DIR__.'/data/matches.json'), true);

        DB::transaction(function () use ($players, $matches) {
            DB::table('players')->insert($players);

            foreach (array_chunk($matches, 100) as $chunk) {
                DB::table('matches')->insert($chunk);
            }

            app(MatchService::class)->recalculateAllPlayerTotals();
        });
    }
}
