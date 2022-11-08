<?php

// Copyright (c) ppy Pty Ltd <contact@ppy.sh>. Licensed under the GNU Affero General Public License v3.0.
// See the LICENCE file in the repository root for full licence text.

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBeatmapCreators extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('beatmap_creators', function (Blueprint $table) {
            $table->unsignedMediumInteger('beatmap_id');
            $table->unsignedInteger('creator_id');

            $table->primary(['beatmap_id', 'creator_id']);
            $table
                ->foreign('beatmap_id')
                ->references('beatmap_id')
                ->on('osu_beatmaps')
                ->cascadeOnDelete();
            $table
                ->foreign('creator_id')
                ->references('user_id')
                ->on('phpbb_users')
                ->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('beatmap_creators');
    }
}
