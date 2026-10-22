<?php

namespace App\Http\Controllers;

use App\Models\GameMatch;
use App\Models\Image;
use App\Models\Player;
use App\Services\StatsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __construct(private readonly StatsService $stats) {}

    public function __invoke(Request $request): View
    {
        $now = now();
        $currentYear = $now->format('Y');
        $currentDate = $now->format('Y-m-d');

        $season = $this->valid($request, 'season', 'digits:4') ?? $currentYear;
        $reportDate = $this->valid($request, 'report_fecha', 'date_format:Y-m-d') ?? $currentDate;
        $filterDate = $this->valid($request, 'fecha_filtro', 'date_format:Y-m-d');
        $filterPlayer = (int) $this->valid($request, 'player_filtro', 'integer');

        $ranking = $this->stats->seasonRanking($season);
        $daily = $this->stats->dailyReport($reportDate);
        $topPlayerIds = $daily['ranking']->take(3)->pluck('id')->all();
        $dailyTopImages = [];

        foreach ($daily['matches']->sortByDesc('fecha') as $dailyMatch) {
            foreach ([[$dailyMatch->ganador_id, $dailyMatch->ganadorImage], [$dailyMatch->perdedor_id, $dailyMatch->perdedorImage]] as [$playerId, $image]) {
                if (in_array($playerId, $topPlayerIds, true) && ! isset($dailyTopImages[$playerId]) && $image) {
                    $dailyTopImages[$playerId] = $image->url;
                }
            }
        }

        foreach (Player::with('image')->whereIn('id', $topPlayerIds)->get() as $player) {
            $dailyTopImages[$player->id] ??= $player->image?->url;
        }

        $matches = GameMatch::query()
            ->with(['ganador:id,nombre', 'perdedor:id,nombre', 'registradoPor:id,name', 'ganadorImage', 'perdedorImage'])
            ->inSeason($season)
            ->when($filterDate, fn ($q) => $q->onDate($filterDate))
            ->when($filterPlayer > 0, fn ($q) => $q->where(
                fn ($w) => $w->where('ganador_id', $filterPlayer)->orWhere('perdedor_id', $filterPlayer)
            ))
            ->whereNotNull('ganador_id')
            ->whereNotNull('perdedor_id')
            ->orderByDesc('id')
            ->get();

        return view('dashboard', [
            'season' => $season,
            'availableSeasons' => $this->stats->availableSeasons($currentYear, $season),
            'players' => Player::orderBy('nombre')->get(['id', 'nombre']),
            'images' => Image::orderBy('nombre')->get(),
            'rankings' => $ranking,
            'rankingByPlayerId' => $ranking->pluck('ranking', 'id'),
            'seasonStats' => $this->stats->seasonStats($season),
            'reportDate' => $reportDate,
            'dailyRankings' => $daily['ranking'],
            'dailyTopImages' => $dailyTopImages,
            'dailyMatches' => $daily['matches'],
            'dailyWinRateChanges' => $daily['winRateChanges'],
            'matches' => $matches,
            'matchWinRateChanges' => $this->stats->winRateChanges($matches),
            'filterPlayer' => $filterPlayer,
            'filterDate' => $filterDate,
            'currentDate' => $currentDate,
            'currentDateTimeLocal' => $now->format('Y-m-d\TH:i'),
            'openDailyReport' => $request->has('report_fecha'),
        ]);
    }

    private function valid(Request $request, string $key, string $rule): ?string
    {
        $value = $request->query($key);

        if (! is_string($value) || $value === '') {
            return null;
        }

        return Validator::make([$key => $value], [$key => $rule])->passes() ? $value : null;
    }
}
