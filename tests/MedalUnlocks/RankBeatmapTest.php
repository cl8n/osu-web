<?php

// Copyright (c) ppy Pty Ltd <contact@ppy.sh>. Licensed under the GNU Affero General Public License v3.0.
// See the LICENCE file in the repository root for full licence text.

declare(strict_types=1);

namespace Tests\MedalUnlocks;

use App\Listeners\MedalUnlocks\RankBeatmap;
use App\Models\Beatmap;
use App\Models\Beatmapset;

class RankBeatmapTest extends MedalUnlockTestCase
{
    protected static function getMedalUnlockClass(): string
    {
        return RankBeatmap::class;
    }

    public function testUnlock(): void
    {
        $beatmapset = Beatmapset::factory()
            ->owner($this->user)
            ->qualified()
            ->has(Beatmap::factory()->qualified()->state(['user_id' => $this->user]))
            ->create();

        $this->resetMedalProgress();

        $beatmapset->rank();

        $this->assertMedalUnlockQueued();
        $this->assertMedalUnlockedWithBeatmap($beatmapset->beatmaps->first());
    }

    public function testUpdateUnrelatedBeatmapsetProperty(): void
    {
        $beatmapset = Beatmapset::factory()
            ->owner($this->user)
            ->has(Beatmap::factory()->ranked()->state(['user_id' => $this->user]))
            ->create(['approved' => Beatmapset::STATES['ranked']]);

        $this->resetMedalProgress();

        $beatmapset->update(['play_count' => $beatmapset->play_count + 1]);

        $this->assertMedalUnlockQueued(false);
        $this->assertMedalUnlocked(false);
    }
}
