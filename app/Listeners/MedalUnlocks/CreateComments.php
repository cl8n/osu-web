<?php

// Copyright (c) ppy Pty Ltd <contact@ppy.sh>. Licensed under the GNU Affero General Public License v3.0.
// See the LICENCE file in the repository root for full licence text.

declare(strict_types=1);

namespace App\Listeners\MedalUnlocks;

use App\Events\ModelCreated;
use App\Models\Comment;
use App\Models\User;
use Illuminate\Support\Collection;

abstract class CreateComments extends MedalUnlock
{
    protected ModelCreated $event;

    abstract public static function getCommentCount(): int;

    public static function getMedalSlug(): string
    {
        return 'create-comments-'.static::getCommentCount();
    }

    protected function getApplicableUsers(): Collection|User|array
    {
        return $this->event->model->user;
    }

    protected function shouldHandle(): bool
    {
        return $this->event->model instanceof Comment;
    }

    protected function shouldUnlockForUser(User $user): bool
    {
        return $user->comments()->withoutTrashed()->count() >= static::getCommentCount();
    }
}
