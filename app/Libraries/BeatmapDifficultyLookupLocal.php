<?php

// Copyright (c) ppy Pty Ltd <contact@ppy.sh>. Licensed under the GNU Affero General Public License v3.0.
// See the LICENCE file in the repository root for full licence text.

namespace App\Libraries;

use App\Models\Beatmap;
use App\Models\BeatmapDifficulty;
use App\Models\BeatmapDifficultyAttrib;

class BeatmapDifficultyLookupLocal extends BeatmapDifficultyLookup
{
    public function __construct()
    {
    }

    public function getAttributes(int $beatmapId, string $playmode, ?array $mods = null): ?array
    {
        $attributes = [];
        $databaseAttributes = BeatmapDifficultyAttrib
            ::where('beatmap_id', $beatmapId)
            ->where('mode', Beatmap::modeInt($playmode))
            ->where('mods', ModsHelper::toBitset($mods))
            ->get();

        foreach ($databaseAttributes as $databaseAttribute) {
            switch ($databaseAttribute->attrib_id) {
                case BeatmapDifficultyAttrib::ATTRIB_ID_AIM:
                    // osu!catch uses aim attribute for star rating
                    // https://github.com/ppy/osu/blob/154460845b77e3f7f8290d1dd210a6f4da379c1e/osu.Game.Rulesets.Catch/Difficulty/CatchDifficultyAttributes.cs#L21
                    if ($playmode === 'fruits') {
                        $attributes['starRating'] = $databaseAttribute->value;
                    }
                    break;
                case BeatmapDifficultyAttrib::ATTRIB_ID_MAX_COMBO:
                    $attributes['maxCombo'] = (int) $databaseAttribute->value;
                    break;
                case BeatmapDifficultyAttrib::ATTRIB_ID_DIFFICULTY:
                    $attributes['starRating'] = $databaseAttribute->value;
                    break;
            }
        }

        return $attributes;
    }

    public function getStarRating(int $beatmapId, string $playmode, ?array $mods = null): ?float
    {
        return BeatmapDifficulty
            ::where([
                'beatmap_id' => $beatmapId,
                'mode' => Beatmap::modeInt($playmode),
                'mods' => ModsHelper::toBitset($mods),
            ])
            ->first()
            ?->diff_unified;
    }
}
