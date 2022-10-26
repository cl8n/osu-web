<?php

// Copyright (c) ppy Pty Ltd <contact@ppy.sh>. Licensed under the GNU Affero General Public License v3.0.
// See the LICENCE file in the repository root for full licence text.

namespace App\Libraries;

use Exception;

class WikiPreviewManager
{
    private string $repositoryUrl;
    private string $webhookSecret;

    public function __construct()
    {
        $this->repositoryUrl = env('WIKI_REPOSITORY');
        $this->webhookSecret = env('WIKI_PREVIEW_GITHUB_WEBHOOK_SECRET');

        if (!present($this->repositoryUrl)) {
            throw new Exception('Invalid wiki repository');
        }

        if (!present($this->webhookSecret)) {
            throw new Exception('Invalid webhook secret');
        }
    }

    public function getBranch(): string
    {
        $pullRequestId = request()->session()->get('wiki_preview_pull_request');

        return $pullRequestId === null
            ? 'master'
            : "pull/{$pullRequestId}";
    }

    public function getGithubWebhookSecret(): string
    {
        return $this->webhookSecret;
    }

    public function handleGithubPushEvent(array $data): void
    {

    }

    public function setPullRequest(?int $pullRequestId): void
    {
        request()->session()->put('wiki_preview_pull_request', $pullRequestId);
    }
}
