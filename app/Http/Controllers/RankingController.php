<?php

namespace App\Http\Controllers;

use App\Models\Player;
use App\Services\StatsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\View\View;

class RankingController extends Controller
{
    public function __construct(private readonly StatsService $stats) {}

    public function __invoke(Request $request): View
    {
        $currentYear = now()->format('Y');
        $requested = $request->query('season');

        $season = is_string($requested) && Validator::make(['s' => $requested], ['s' => 'digits:4'])->passes()
            ? $requested
            : $currentYear;

        $rankings = $this->stats->seasonStats($season);
        $images = Player::with('image')->whereIn('id', $rankings->pluck('id'))->get()->pluck('image.url', 'id');

        return view('ranking.index', [
            'season' => $season,
            'availableSeasons' => $this->stats->availableSeasons($currentYear, $season),
            'rankings' => $rankings,
            'images' => $images,
            'trends' => $this->stats->winPercentTrends($season),
        ]);
    }
}
