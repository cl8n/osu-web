<?php

// Copyright (c) ppy Pty Ltd <contact@ppy.sh>. Licensed under the GNU Affero General Public License v3.0.
// See the LICENCE file in the repository root for full licence text.

namespace App\Models;

/**
 * @property int $attrib_id
 * @property int $beatmap_id
 * @property int $mode
 * @property int $mods
 * @property float|null $value
 */
class BeatmapDifficultyAttrib extends Model
{
    const NO_MODS = 0;

    const ATTRIB_ID_AIM = 1;
    const ATTRIB_ID_MAX_COMBO = 9;
    const ATTRIB_ID_DIFFICULTY = 11;

    protected $table = 'osu_beatmap_difficulty_attribs';
    protected $primaryKey = null;

    public $timestamps = false;

    public function scopeMode($query, $mode)
    {
        return $query->where('mode', $mode);
    }

    public function scopeMaxCombo($query)
    {
        return $query->where('attrib_id', static::ATTRIB_ID_MAX_COMBO);
    }

    public function scopeNoMods($query)
    {
        return $query->where('mods', static::NO_MODS);
    }
}
