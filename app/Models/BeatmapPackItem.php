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

namespace App\Models;

use App\Traits\Validatable;

/**
 * @property Beatmapset $beatmapset
 * @property int $beatmapset_id
 * @property int $item_id
 * @property BeatmapPack $pack
 * @property int $pack_id
 */
class BeatmapPackItem extends Model
{
    use Validatable;

    protected $table = 'osu_beatmappacks_items';
    protected $primaryKey = 'item_id';
    public $timestamps = false;

    public function pack()
    {
        return $this->belongsTo(BeatmapPack::class, 'pack_id');
    }

    public function beatmapset()
    {
        return $this->belongsTo(Beatmapset::class, 'beatmapset_id');
    }

    public function isValid() : bool
    {
        $this->validationErrors()->reset();

        if ($this->beatmapset === null) {
            $this->validationErrors()->add('beatmapset_id', '.invalid_beatmapset');
        }

        if ($this->pack === null) {
            $this->validationErrors()->add('pack_id', '.invalid_pack');
        }

        if ($this->validationErrors()->isEmpty()
            && $this->pack->playmode !== null
            && !$this->beatmapset->playmodes()->contains($this->pack->playmode)) {
            $this->validationErrors()->add('beatmapset_id', '.wrong_playmode');
        }

        return $this->validationErrors()->isEmpty();
    }

    public function validationErrorsTranslationPrefix() : string
    {
        return 'beatmap_pack_item';
    }

    public function save(array $options = []) : bool
    {
        if (!$this->isValid()) {
            return false;
        }

        return parent::save($options);
    }
}
