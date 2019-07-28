<?php

/**
 *    Copyright (c) ppy Pty Ltd <contact@ppy.sh>.
 *
 *    This file is part of osu!web. osu!web is distributed with the hope of
 *    attracting more community contributions to the core ecosystem of osu!.
 *
 *    osu!web is free software: you can redistribute it and/or modify
 *    it under the terms of the Affero GNU General Public License version 3
 *    as published by the Free Software Foundation.
 *
 *    osu!web is distributed WITHOUT ANY WARRANTY; without even the implied
 *    warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 *    See the GNU Affero General Public License for more details.
 *
 *    You should have received a copy of the GNU Affero General Public License
 *    along with osu!web.  If not, see <http://www.gnu.org/licenses/>.
 */

namespace App\Http\Controllers\Admin;

use App\Models\Achievement;

class AchievementsController extends Controller
{
    private $params = null;

    public function index()
    {
        $achievements = Achievement::all();

        return view('admin.achievements.index')
            ->with('achievements', json_collection($achievement, 'Achievement'));
    }

    public function store()
    {
        $achievement = Achievement::create($this->achievementParams());

        return json_item($achievement, 'Achievement');
    }

    public function update($id)
    {
        $achievement = Achievement::findOrFail($id);
        $achievement->update($this->achievementParams());

        return json_item($achievement, 'Achievement');
    }

    private function achievementParams()
    {
        if ($this->params === null) {
            $this->params = get_params(Request::input(), null, [
                'achievement_id:int',
                'description:string',
                'enabled:bool',
                'grouping:string',
                'mode:int',
                'name:string',
                'ordering:int',
                'pack_id:int',
                'progression:int',
                'quest_instructions:string',
                'slug:string',
            ]);
        }

        return $this->params;
    }
}
