<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCustomDifficultyRatingToBeatmaps extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('osu_beatmaps', function (Blueprint $table) {
            $table->unsignedTinyInteger('difficulty_rating_custom')->nullable()->default(null);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('osu_beatmaps', function (Blueprint $table) {
            $table->dropColumn('difficulty_rating_custom');
        });
    }
}
