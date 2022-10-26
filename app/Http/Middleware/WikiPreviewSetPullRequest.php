<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class WikiPreviewSetPullRequest
{
    public function handle(Request $request, Closure $next)
    {
        if (!$request->isMethod('GET')) {
            return $next($request);
        }

        $pullRequestIdQuery = $request->query('wiki-pr');

        if ($pullRequestIdQuery === null) {
            return $next($request);
        }

        $pullRequestId = get_int($pullRequestIdQuery);

        if ($pullRequestId !== null || $pullRequestIdQuery === 'master') {
            app('wiki-preview-manager')->setPullRequest($pullRequestId);
        }

        return ujs_redirect($request->fullUrlWithoutQuery('wiki-pr'));
    }
}
