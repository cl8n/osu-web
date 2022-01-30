<?php

// Copyright (c) ppy Pty Ltd <contact@ppy.sh>. Licensed under the GNU Affero General Public License v3.0.
// See the LICENCE file in the repository root for full licence text.

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Beatmap;
use App\Models\Score\Best\Model as ScoreBest;

class RecordsController extends Controller
{
    const DEFAULT_RECORD_TYPE = 'scores-performance';
    const RECORD_TYPES = ['scores-performance'];

    private string $mode;
    private string $type;

    public function index($mode = null, $type = null)
    {
        if ($mode === null) {
            return ujs_redirect(route('records', [
                'mode' => default_mode(),
                'type' => static::DEFAULT_RECORD_TYPE,
            ]));
        }

        if (!Beatmap::isModeValid($mode)) {
            abort(404);
        }

        if ($type === null) {
            ujs_redirect(route('records', [
                'mode' => $mode,
                'type' => static::DEFAULT_RECORD_TYPE,
            ]));
        }

        abort_unless(in_array($type, static::RECORD_TYPES, true), 404);

        $this->mode = $mode;
        $this->type = $type;

        $method = camel_case($type);
        return $this->$method();
    }

    private function indexView(array $data)
    {
        return ext_view('records.index', [
            'recordsIndexJson' => array_merge([
                'mode' => $this->mode,
                'type' => $this->type,
            ], $data),
        ]);
    }

    private function scoresPerformance()
    {
        $scores = ScoreBest::getClassByString($this->mode)
            ::where('hidden', false)
            ->orderBy('pp', 'DESC')
            ->limit(50)
            ->get();

        $scores = json_collection($scores, 'Score', [
            'beatmap',
            'beatmapset',
            'user',
        ]);

        return $this->indexView(compact('scores'));
    }
}
