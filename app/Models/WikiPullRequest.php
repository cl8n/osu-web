<?php

// Copyright (c) ppy Pty Ltd <contact@ppy.sh>. Licensed under the GNU Affero General Public License v3.0.
// See the LICENCE file in the repository root for full licence text.

namespace App\Models;

use Carbon\Carbon;

/**
 * @property string $author_avatar_url
 * @property string $author_name
 * @property int $id
 * @property \Carbon\Carbon $last_push_at
 * @property \Carbon\Carbon $opened_at
 * @property string $title
 */
class WikiPullRequest extends Model
{
    public bool $incrementing = false;
    public bool $timestamps = false;

    protected array $dates = ['last_push_at', 'opened_at'];

    public static function createFromGithub(array $event): static
    {
        return static::create([
            'author_avatar_url' => '',
            'author_name' => '',
            'created_at' => Carbon::parse(''),
            'id' => 0,
            'title' => '',
            'updated_at' => Carbon::parse(''),
        ]);
    }
}
