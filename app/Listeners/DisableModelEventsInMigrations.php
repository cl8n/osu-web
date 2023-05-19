<?php

// Copyright (c) ppy Pty Ltd <contact@ppy.sh>. Licensed under the GNU Affero General Public License v3.0.
// See the LICENCE file in the repository root for full licence text.

declare(strict_types=1);

namespace App\Listeners;

use App\Models\Model;
use Closure;
use Illuminate\Database\Events\MigrationsEnded;
use Illuminate\Database\Events\MigrationsStarted;
use Illuminate\Events\NullDispatcher;

/**
 * Disable model events while migrations are running.
 */
class DisableModelEventsInMigrations
{
    public function handleMigrationsEnded(): void
    {
        $dispatcher = Model::getEventDispatcher();

        if ($dispatcher instanceof NullDispatcher) {
            $getOriginalDispatcher = Closure::bind(
                fn (NullDispatcher $dispatcher) => $dispatcher->dispatcher,
                null,
                NullDispatcher::class,
            );

            Model::setEventDispatcher($getOriginalDispatcher($dispatcher));
        }
    }

    public function handleMigrationsStarted(): void
    {
        Model::setEventDispatcher(new NullDispatcher(Model::getEventDispatcher()));
    }

    public function subscribe(): array
    {
        return [
            MigrationsEnded::class => 'handleMigrationsEnded',
            MigrationsStarted::class => 'handleMigrationsStarted',
        ];
    }
}
