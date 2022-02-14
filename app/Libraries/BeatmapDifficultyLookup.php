<?php

// Copyright (c) ppy Pty Ltd <contact@ppy.sh>. Licensed under the GNU Affero General Public License v3.0.
// See the LICENCE file in the repository root for full licence text.

namespace App\Libraries;

use App\Models\Beatmap;
use GuzzleHttp\Client;

class BeatmapDifficultyLookup
{
    private Client $httpClient;

    public function __construct()
    {
        $this->httpClient = new Client(['base_uri' => config('osu.beatmap_difficulty_lookup.server')]);
    }

    //{"aimStrain":4.578457906273151,"speedStrain":6.196480875019435,"approachRate":10.333333333333332,"overallDifficulty":9.777777777777779,"hitCircleCount":1646,"spinnerCount":2,"mods":[{"acronym":"DT","speedChange":1.5,"settingDescription":"","ranked":false}],"skills":[{},{}],"starRating":11.474577136820706,"maxCombo":2385}

    /**
     * @return array{aimStrain: float, speedStrain: float, approachRate: float, overallDifficulty: float, hitCircleCount: int, spinnerCount: int, mods: array[], skills: array[], starRating: float, maxCombo: float}|null
     */
    public function getAttributes(int $beatmapId, string $playmode, ?array $mods = null): ?array
    {
        return json_decode(
            $this->requestDifficulty('attributes', $beatmapId, $playmode, $mods),
            true,
        );
    }

    public function getStarRating(int $beatmapId, string $playmode, ?array $mods = null): ?float
    {
        return get_float(
            $this->requestDifficulty('rating', $beatmapId, $playmode, $mods),
        );
    }

    private function requestDifficulty(string $uri, int $beatmapId, string $playmode, ?array $mods): string
    {
        $requestBody = [
            'beatmap_id' => $beatmapId,
            'ruleset_id' => Beatmap::modeInt($playmode),
        ];

        if ($mods !== null) {
            $requestBody['mods'] = array_map(fn (string $mod) => ['acronym' => $mod], $mods);
        }

        return $this
            ->httpClient
            ->post($uri, ['json' => $requestBody])
            ->getBody()
            ->getContents();
    }
}
