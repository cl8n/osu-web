<?php

// Copyright (c) ppy Pty Ltd <contact@ppy.sh>. Licensed under the GNU Affero General Public License v3.0.
// See the LICENCE file in the repository root for full licence text.

namespace Tests\Models\Multiplayer;

use App\Models\Multiplayer\PlaylistItem;
use App\Models\Multiplayer\Room;
use App\Models\Multiplayer\ScoreLink;
use App\Models\Multiplayer\UserScoreAggregate;
use App\Models\User;
use Tests\TestCase;

class UserScoreAggregateTest extends TestCase
{
    private $room;

    public function testStartingPlayIncreasesAttempts()
    {
        $user = User::factory()->create();
        $playlistItem = $this->playlistItem();

        $this->room->startPlay($user, $playlistItem, 0);
        $agg = UserScoreAggregate::new($user, $this->room);

        $this->assertSame(1, $agg->attempts);
        $this->assertSame(0, $agg->completed);
    }

    public function testInCompleteScoresAreNotCounted()
    {
        $user = User::factory()->create();
        $playlistItem = $this->playlistItem();
        $agg = UserScoreAggregate::new($user, $this->room);

        $scoreLink = ScoreLink::factory()
            ->state([
                'room_id' => $this->room,
                'playlist_item_id' => $playlistItem,
                'user_id' => $user,
            ])->create();

        $agg->addScoreLink($scoreLink);
        $result = json_item($agg, 'Multiplayer\UserScoreAggregate');

        $this->assertSame(0, $result['completed']);
        $this->assertSame(0, $result['total_score']);
    }

    public function testFailedScoresAreAttemptsOnly()
    {
        $user = User::factory()->create();
        $playlistItem = $this->playlistItem();
        $agg = UserScoreAggregate::new($user, $this->room);

        $agg->addScoreLink(
            ScoreLink
                ::factory()
                ->state([
                    'room_id' => $this->room,
                    'playlist_item_id' => $playlistItem,
                    'user_id' => $user,
                ])->failed()
                ->create()
        );

        $agg->addScoreLink(
            ScoreLink::factory()
                ->state([
                    'room_id' => $this->room,
                    'playlist_item_id' => $playlistItem,
                    'user_id' => $user,
                ])->completed([], ['passed' => true, 'total_score' => 1])
                ->create()
        );

        $result = json_item($agg, 'Multiplayer\UserScoreAggregate');

        $this->assertSame(1, $result['completed']);
        $this->assertSame(1, $result['total_score']);
    }

    public function testPassedScoresIncrementsCompletedCount()
    {
        $user = User::factory()->create();
        $playlistItem = $this->playlistItem();
        $agg = UserScoreAggregate::new($user, $this->room);

        $agg->addScoreLink(
            ScoreLink::factory()
                ->state([
                    'room_id' => $this->room,
                    'playlist_item_id' => $playlistItem,
                    'user_id' => $user,
                ])->completed([], ['passed' => true, 'total_score' => 1])
                ->create()
        );

        $result = json_item($agg, 'Multiplayer\UserScoreAggregate');

        $this->assertSame(1, $result['completed']);
        $this->assertSame(1, $result['total_score']);
    }

    public function testPassedScoresAreAveraged()
    {
        $user = User::factory()->create();
        $playlistItem = $this->playlistItem();
        $playlistItem2 = $this->playlistItem();

        $agg = UserScoreAggregate::new($user, $this->room);
        $agg->addScoreLink(ScoreLink::factory()
            ->state([
                'room_id' => $this->room,
                'playlist_item_id' => $playlistItem,
                'user_id' => $user,
            ])->completed([], [
                'total_score' => 1,
                'passed' => false,
            ])->create());

        $agg->addScoreLink(ScoreLink::factory()
            ->state([
                'room_id' => $this->room,
                'playlist_item_id' => $playlistItem,
                'user_id' => $user,
            ])->completed([], [
                'total_score' => 1,
                'accuracy' => 0.3,
                'passed' => false,
            ])->create());

        $agg->addScoreLink(ScoreLink::factory()
            ->state([
                'room_id' => $this->room,
                'playlist_item_id' => $playlistItem,
                'user_id' => $user,
            ])->completed([], [
                'total_score' => 1,
                'accuracy' => 0.5,
                'passed' => true,
            ])->create());

        $agg->addScoreLink(ScoreLink::factory()
            ->state([
                'room_id' => $this->room,
                'playlist_item_id' => $playlistItem2,
                'user_id' => $user,
            ])->completed([], [
                'total_score' => 1,
                'accuracy' => 0.8,
                'passed' => true,
            ])->create());

        $result = json_item($agg, 'Multiplayer\UserScoreAggregate');

        $this->assertSame(0.65, $result['accuracy']);
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->room = Room::factory()->create();
    }

    private function playlistItem()
    {
        return PlaylistItem::factory()->create([
            'room_id' => $this->room,
        ]);
    }
}
