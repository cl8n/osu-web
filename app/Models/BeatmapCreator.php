<?php

// Copyright (c) ppy Pty Ltd <contact@ppy.sh>. Licensed under the GNU Affero General Public License v3.0.
// See the LICENCE file in the repository root for full licence text.

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Intermediate model for relating beatmaps to creators.
 *
 * @property-read \App\Models\Beatmap $beatmap
 * @property int $beatmap_id
 * @property-read \App\Models\User $creator
 * @property int $creator_id
 */
class BeatmapCreator extends Model
{
    public $incrementing = false;
    public $timestamps = false;

    protected $primaryKey = ':composite';
    protected $primaryKeys = ['beatmap_id', 'creator_id'];

    public function beatmap(): BelongsTo
    {
        return $this->belongsTo(Beatmap::class, 'beatmap_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'creator_id');
    }
}
