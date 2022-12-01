<?php

// Copyright (c) ppy Pty Ltd <contact@ppy.sh>. Licensed under the GNU Affero General Public License v3.0.
// See the LICENCE file in the repository root for full licence text.

declare(strict_types=1);

namespace App\View\Components\Docs;

use Illuminate\View\Component;

class ScopeBadge extends Component
{
    public function __construct(public string $scope, public bool $anchor = false)
    {
    }

    public function render(): string
    {
        $scopeLowercase = strtolower($this->scope);
        $extraAttribute = $this->anchor
            ? "name=\"scope-{$scopeLowercase}\""
            : "href=\"#scope-{$scopeLowercase}\"";

        return "<a class=\"badge badge-scope badge-scope-{$scopeLowercase}\" {$extraAttribute}>{$this->scope}</a>";
    }
}
