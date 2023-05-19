<?php

// Copyright (c) ppy Pty Ltd <contact@ppy.sh>. Licensed under the GNU Affero General Public License v3.0.
// See the LICENCE file in the repository root for full licence text.

declare(strict_types=1);

namespace App\Listeners\MedalUnlocks;

use App\Events\ModelUpdating;
use App\Models\Beatmap;
use App\Models\Beatmapset;
use App\Models\User;
use Illuminate\Support\Collection;

class RankBeatmap extends MedalUnlock
{
    protected ModelUpdating $event;

    public static function getMedalSlug(): string
    {
        return 'rank-beatmap';
    }

    protected function getApplicableUsers(): Collection|User|array
    {
        return $this->event->model->beatmaps->pluck('user');
    }

    protected function getBeatmapForUser(User $user): ?Beatmap
    {
        return $this->event->model->beatmaps->firstWhere('user_id', $user->getKey());
    }

    protected function shouldHandle(): bool
    {
        $model = $this->event->model;

        return $model instanceof Beatmapset
            && $model->isDirty('approved')
            && $model->isRanked();
    }

    protected function shouldUnlockForUser(User $user): bool
    {
        return true;
    }
}
