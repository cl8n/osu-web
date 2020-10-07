<?php

// Copyright (c) ppy Pty Ltd <contact@ppy.sh>. Licensed under the GNU Affero General Public License v3.0.
// See the LICENCE file in the repository root for full licence text.

namespace App\Http\Controllers;

use App\Models\Group;
use App\Models\UserGroupEvent;

class GroupHistoryController extends Controller
{
    public function index()
    {
        $filters = get_params(request()->input(), null, [
            'group_id:int',
            'user_id:int',
        ]);

        $paginator = UserGroupEvent::visible()->with('user')->paginate();

        $jsonChunks = [
            'events' => json_collection($paginator->getCollection(), 'UserGroupEvent', ['user']),
            'groups' => json_collection(app('groups')->all(), 'Group'),
        ];

        return ext_view('groups_history.index', compact('jsonChunks', 'paginator'));
    }
}
