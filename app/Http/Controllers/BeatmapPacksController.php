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

namespace App\Http\Controllers;

use App\Models\Beatmap;
use App\Models\BeatmapPack;
use App\Models\BeatmapPackItem;
use Auth;
use Carbon\Carbon;
use Request;

class BeatmapPacksController extends Controller
{
    protected $section = 'beatmaps';
    private const PER_PAGE = 20;
    private $params = [];

    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            priv_check('BeatmapPackManage')->ensureCan();

            return $next($request);
        }, ['except' => ['index', 'show', 'raw']]);

        return parent::__construct();
    }

    public function index()
    {
        $type = presence(Request::input('type')) ?? BeatmapPack::DEFAULT_TYPE;
        $packs = BeatmapPack::getPacks($type);
        if ($packs === null) {
            abort(404);
        }

        return view('packs.index')
            ->with('packs', $packs->paginate(static::PER_PAGE)->appends(['type' => $type]))
            ->with('type', $type);
    }

    public function show($idOrTag)
    {
        if (is_numeric($idOrTag)) {
            $pack = BeatmapPack::findOrFail($idOrTag);
        } else {
            $pack = BeatmapPack::where('tag', $idOrTag)->firstOrFail();
        }

        return ujs_redirect($this->indexLink($pack));
    }

    public function raw($id)
    {
        $pack = BeatmapPack::findOrFail($id);
        $mode = Beatmap::modeStr($pack->playmode ?? 0);

        $sets = $pack
            ->beatmapsets()
            ->select()
            ->withHasCompleted($pack->playmode ?? 0, Auth::user())
            ->get();

        return view('packs.show', compact('pack', 'sets', 'mode'));
    }

    public function create()
    {
        return view('packs.edit');
    }

    public function edit($id)
    {
        $pack = BeatmapPack::with('items.beatmapset')->findOrFail($id);

        return view('packs.edit', compact('pack'));
    }

    public function store()
    {
        $pack = new BeatmapPack($this->packParams());
        $setIds = get_arr(Request::input('beatmapset_ids'), 'get_int');

        try {
            $this->getConnection()->transaction(function () use ($pack, $setIds) {
                $pack->save();

                foreach ($setIds as $setId) {
                    $pack->items()->saveOrExplode(new BeatmapPackItem(['beatmapset_id' => $setId]));

                    /*
                    new BeatmapPackItem([
                        'beatmapset_id' => $setId,
                        'pack_id' => $pack->getKey(),
                    ])->saveOrExplode();
                    */
                }
            });
        } catch (ModelNotSavedException $e) {
            return error_popup($e->getMessage());
        }

        return ujs_redirect($this->indexLink($pack));
    }

    public function update($id)
    {
        $pack = BeatmapPack::findOrFail($id);
        $pack->update($this->packParams());

        return json_item($this, 'BeatmapPack');
    }

    public function storeItem($id)
    {
        $pack = BeatmapPack::findOrFail($id);
        $setId = get_int(Request::input('beatmapset_id'));
        $item = new BeatmapPackItem(['beatmapset_id' => $setId]);

        $pack->items()->saveOrExplode($item); // See line 110

        return json_item($item->beatmapset, 'BeatmapsetCompact');
    }

    public function destroyItem($id, $itemId)
    {
        BeatmapPack::findOrFail($id)
            ->items()
            ->destroy($itemId);

        return response([], 204);
    }

    public function storeAchievement($id)
    {

    }

    private function indexLink(BeatmapPack $pack) : string
    {
        $type = $pack->type();
        $indexInPagination = BeatmapPack::getPacks($type)->get()->search($pack);
        $page = intdiv($indexInPagination, static::PER_PAGE) + 1;

        return route('packs.index', ['type' => $type, 'page' => $page === 1 ? null : $page])
            .'#pack-'.$pack->getKey();
    }

    private function packParams() : array
    {
        if (!isset($this->params['pack'])) {
            $this->params['pack'] = get_params(Request::input(), 'pack', [
                'author:string',
                'date:string',
                'name:string',
                'playmode:int',
                'tag:string',
                'url:string',
            ]);
        }

        return $this->params['pack'];
    }

    private function achievementParams() : array
    {
        if (!isset($this->params['achievement'])) {
            $this->params['achievement'] = get_params(Request::input(), 'achievement', [
                //'achievement_id:int',
                'description:string',
                //'enabled:bool',
                'grouping:string',
                'mode:int',
                'name:string',
                'ordering:int',
                // TODO 'pack_id:int',
                'progression:int',
                'quest_instructions:string',
                'slug:string',
            ]);
        }

        return $this->params['achievement'];
    }
}
