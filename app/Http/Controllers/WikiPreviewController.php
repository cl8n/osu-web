<?php

// Copyright (c) ppy Pty Ltd <contact@ppy.sh>. Licensed under the GNU Affero General Public License v3.0.
// See the LICENCE file in the repository root for full licence text.

namespace App\Http\Controllers;

class WikiPreviewController extends Controller
{
    public function handleGithubWebhook()
    {
        $webhookSecret = app('wiki-preview-manager')->getGithubWebhookSecret();

        abort_if(
            request()->header('X-GitHub-Event') !== 'push',
            422,
            'Invalid webhook event',
        );

        $signatureHeader = explode('=', request()->header('X-Hub-Signature'));

        abort_if(
            count($signatureHeader) !== 2,
            422,
            'Invalid signature header',
        );

        [$algorithm, $signature] = $signatureHeader;

        abort_if(
            !in_array($algorithm, hash_hmac_algos(), true),
            422,
            'Unknown signature algorithm',
        );

        $hash = hash_hmac($algorithm, request()->getContent(), $webhookSecret);

        abort_if(
            !hash_equals((string) $hash, (string) $signature),
            401,
        );

        app('wiki-preview-manager')
            ->handleGithubPushEvent(request()->json()->all());

        return response(null, 204);
    }

    public function setPullRequest()
    {
        app('wiki-preview-manager')
            ->setPullRequest(get_int(request()->input('pr')));

        return response(null, 204);
    }
}
