<?php

// Copyright (c) ppy Pty Ltd <contact@ppy.sh>. Licensed under the GNU Affero General Public License v3.0.
// See the LICENCE file in the repository root for full licence text.

namespace App\Models;

use App\Models\Score\Best\Model as ScoreBest;

/**
 * @property int $high_score_id
 * @property int $high_score_mode
 * @property int $user_id
 */
class PinnedScore extends Model
{
    public $timestamps = false;

    protected $primaryKeys = ['high_score_id', 'high_score_mode'];

    public function scoreBest()
    {
        return $this->belongsTo(ScoreBest::getClass($this->high_score_mode), 'high_score_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
