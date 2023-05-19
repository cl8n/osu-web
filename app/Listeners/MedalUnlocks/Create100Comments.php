<?php

// Copyright (c) ppy Pty Ltd <contact@ppy.sh>. Licensed under the GNU Affero General Public License v3.0.
// See the LICENCE file in the repository root for full licence text.

declare(strict_types=1);

namespace App\Listeners\MedalUnlocks;

class Create100Comments extends CreateComments
{
    public static function getCommentCount(): int
    {
        return 100;
    }
}
