<?php

// Copyright (c) ppy Pty Ltd <contact@ppy.sh>. Licensed under the GNU Affero General Public License v3.0.
// See the LICENCE file in the repository root for full licence text.

namespace App\Transformers;

use App\Models\UserGroupEvent;

class UserGroupEventTransformer extends TransformerAbstract
{
    protected $availableIncludes = [
        'user',
    ];

    public function transform(UserGroupEvent $event)
    {
        return array_merge(
            $event->details,
            [
                'created_at' => json_time($event->created_at),
                'group_id' => $event->group_id,
                'id' => $event->id,
                'type' => $event->type,
            ],
        );
    }

    public function includeUser(UserGroupEvent $event)
    {
        if ($event->user !== null) {
            return $this->item($event->user, new UserCompactTransformer());
        }
    }
}
