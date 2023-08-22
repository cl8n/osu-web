<?php

// Copyright (c) ppy Pty Ltd <contact@ppy.sh>. Licensed under the GNU Affero General Public License v3.0.
// See the LICENCE file in the repository root for full licence text.

namespace App\Jobs;

use App\Exceptions\InvalidNotificationException;
use App\Jobs\Notifications\BroadcastNotificationBase;
use App\Mail\UserNotificationDigest as UserNotificationDigestMail;
use App\Models\Notification;
use App\Models\User;
use App\Models\UserNotification;
use Datadog;
use DB;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;
use Mail;

class UserNotificationDigest implements ShouldQueue
{
    use Queueable, SerializesModels;

    private $fromId;
    private $now;
    private $toId;
    private $user;
    private $watches;

    public function __construct(User $user, int $fromId, int $toId)
    {
        $this->user = $user;
        $this->fromId = $fromId;
        $this->toId = $toId;
        $this->now = now();
    }

    public function handle()
    {
        if (!is_valid_email_format($this->user->user_email)) {
            return;
        }

        $notifications = $this->filterNotifications($this->getNotifications());

        if (empty($notifications)) {
            return;
        }

        // TODO: catch and log errors?
        Mail::to($this->user)->send(new UserNotificationDigestMail($notifications, $this->user));

        Datadog::increment(config('datadog-helper.prefix_web').'.user_notification_digest.mail', 1);
    }

    private function filterNotifications(Collection $notifications)
    {
        // preload the watches and key them for lookup.
        $this->watches = [
            'topics' => $this->user->topicWatches()
                ->whereIn('topic_id', $notifications->where('notifiable_type', '=', 'forum_topic')->pluck('notifiable_id'))
                ->where('mail', true)
                ->where('notify_status', false)
                ->get()
                ->keyBy('topic_id'),
        ];

        $filtered = [];

        foreach ($notifications as $notification) {
            if (!$this->shouldSend($notification)) {
                continue;
            }

            $filtered[] = $notification;
        }

        // bulk update the watches
        DB::transaction(function () {
            $topicIds = $this->watches['topics']->filter(function ($watch) {
                return $watch->isDirty();
            })->keys();

            $this->user->topicWatches()->whereIn('topic_id', $topicIds)->update(['notify_status' => true]);
        });

        return $filtered;
    }

    private function getNotifications()
    {
        $notificationIdsQuery = UserNotification
            ::where('user_id', $this->user->getKey())
            ->where('is_read', false)
            ->hasMailDelivery()
            ->where('id', '>', $this->fromId)
            ->where('id', '<=', $this->toId)
            ->select('notification_id');

        return Notification::whereIn('id', $notificationIdsQuery)->get();
    }

    private function shouldSend(Notification $notification): bool
    {
        try {
            $class = BroadcastNotificationBase::getNotificationClassFromNotification($notification);

            return $class::shouldSendMail($notification, $this->watches, $this->now);
        } catch (InvalidNotificationException $e) {
            log_error($e);

            return false;
        }
    }
}
