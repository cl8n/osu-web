<?php

// Copyright (c) ppy Pty Ltd <contact@ppy.sh>. Licensed under the GNU Affero General Public License v3.0.
// See the LICENCE file in the repository root for full licence text.

namespace App\Libraries;

use App\Http\Middleware\RequireScopes;
use Knuckles\Camel\Output\OutputEndpointData;

class ApidocRouteHelper
{
    private $routeScopes = [];

    /**
     * Get the description of an endpoint, split into two parts by horizontal rule.
     */
    public static function getDescriptions(OutputEndpointData $endpoint): array
    {
        $descriptions = explode("\n---\n", $endpoint->metadata->description ?? '', 2);

        return [$descriptions[0], $descriptions[1] ?? ''];
    }

    /**
     * Get the URI of an endpoint for display in documentation.
     */
    public static function getDisplayUri(OutputEndpointData $endpoint): string
    {
        return static::isApiEndpoint($endpoint)
            ? substr($endpoint->uri, 6)
            : config('app.url').$endpoint->uri;
    }

    /**
     * Get the title of an endpoint for display in documentation.
     */
    public static function getTitle(OutputEndpointData $endpoint): string
    {
        return $endpoint->metadata->title ?: static::getDisplayUri($endpoint);
    }

    public static function instance()
    {
        static $instance;

        if ($instance === null) {
            $instance = new static();
        }

        return $instance;
    }

    /**
     * Whether the endpoint is part of API v2.
     */
    public static function isApiEndpoint(OutputEndpointData $endpoint): bool
    {
        return substr($endpoint->uri, 0, 6) === 'api/v2';
    }

    private static function keyFor(array $methods, string $uri)
    {
        return RouteScopesHelper::keyForMethods($methods).'@'.$uri;
    }

    private static function requiresAuthentication(array $route)
    {
        return !(
            in_array('GET', $route['methods'], true)
            && starts_with("{$route['uri']}/", RequireScopes::NO_TOKEN_REQUIRED)
        );
    }

    private function __construct()
    {
        $routeScopesHelper = new RouteScopesHelper();
        $routeScopesHelper->loadRoutes();

        foreach ($routeScopesHelper->toArray() as $route) {
            // apidoc doesn't contain HEAD.
            if (in_array('HEAD', $route['methods'], true)) {
                $route['methods'] = array_filter($route['methods'], function ($method) {
                    return $method !== 'HEAD';
                });
            }

            if (static::requiresAuthentication($route)) {
                if (empty($route['scopes'])) {
                    $route['scopes'][] = 'lazer'; // not osu!lazer to make the css handling simpler.
                }

                $route['scopes'] = array_filter($route['scopes'], function ($scope) {
                    return $scope !== 'any';
                });

                // anything that will list scopes will require OAuth.
                array_unshift($route['scopes'], 'OAuth');
            } else {
                $route['scopes'] = [];
            }

            $route['auth'] = in_array('auth', $route['middlewares'], true);

            $this->routeScopes[static::keyFor($route['methods'], $route['uri'])] = $route;
        }
    }

    /**
     * Whether the endpoint requires an authenticated user.
     */
    public function getAuth(OutputEndpointData $endpoint): bool
    {
        return $this->routeScopes[static::keyFor($endpoint->httpMethods, $endpoint->uri)]['auth'];
    }

    /**
     * Get the scopes of an endpoint for display in documentation.
     */
    public function getScopeTags(OutputEndpointData $endpoint): array
    {
        return $this->routeScopes[static::keyFor($endpoint->httpMethods, $endpoint->uri)]['scopes'];
    }
}
