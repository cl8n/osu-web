<?php

// Copyright (c) ppy Pty Ltd <contact@ppy.sh>. Licensed under the GNU Affero General Public License v3.0.
// See the LICENCE file in the repository root for full licence text.

namespace App\Console\Commands;

use App\Jobs\Notifications\ForumTopicReply;
use App\Models\Forum\Post;
use App\Models\Forum\Topic;
use App\Models\User;
use Illuminate\Console\Command;
use NumberFormatter;

class CloseLovedVotingTopics extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'forum:close-loved-voting-topics';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Lock and create the final post of Project Loved voting topics.';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $openLovedVotingTopics = Topic::with('pollOptions')
            ->whereNotNull('loved_voting_beatmapset_id')
            ->where('topic_status', Topic::STATUS_UNLOCKED)
            ->get();

        if ($openLovedVotingTopics->isEmpty()) {
            $this->info('There are no open Loved voting topics.');
            return 0;
        }

        $invalidTopics = $openLovedVotingTopics->reject(fn ($topic) => $topic->lovedVoting()->isValid());

        if ($invalidTopics->isNotEmpty()) {
            $invalidTopicsList = $invalidTopics->pluck('topic_id')->join(', ');
            $this->error("Some of the open Loved voting topics are not in a valid format: {$invalidTopicsList}.");
            return 1;
        }

        if ($openLovedVotingTopics->contains(fn ($topic) => $topic->pollEnd()->isFuture())) {
            $this->info('Some of the open Loved voting polls have not ended yet.');
            return 0;
        }

        $banchoBot = User::findOrFail(config('osu.legacy.bancho_bot_user_id'));
        $this->info('Closing Loved voting topics...');
        $progressBar = $this->output->createProgressBar($openLovedVotingTopics->count());

        foreach ($openLovedVotingTopics as $topic) {
            $topic->getConnection()->transaction(function () use ($banchoBot, $topic) {
                $topic->lock();
                $post = Post::createNew($topic, $banchoBot, $this->getReplyBody($topic));

                (new ForumTopicReply($post, $banchoBot))->dispatch();
            });

            $progressBar->advance();
        }

        $progressBar->finish();
        return 0;
    }

    /**
     * Get the content for the final post of a Loved voting topic.
     */
    private function getReplyBody(Topic $topic): string
    {
        $lovedVoting = $topic->lovedVoting();
        $result = $lovedVoting->result();

        $approvalFormatted = i18n_number_format($result['approval'], NumberFormatter::PERCENT, null, 2, 'en');
        $thresholdFormatted = i18n_number_format($lovedVoting->passThreshold(), NumberFormatter::PERCENT, null, 0, 'en');

        return $result['passed']
            ? "This map passed voting with [b]{$approvalFormatted}[/b] approval! It will be moved to the Loved category soon."
            : "This map did not meet the {$thresholdFormatted} voting requirement, having [b]{$approvalFormatted}[/b] approval.";
    }
}
