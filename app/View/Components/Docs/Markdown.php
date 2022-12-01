<?php

// Copyright (c) ppy Pty Ltd <contact@ppy.sh>. Licensed under the GNU Affero General Public License v3.0.
// See the LICENCE file in the repository root for full licence text.

declare(strict_types=1);

namespace App\View\Components\Docs;

use Closure;
use Illuminate\View\Component;
use Knuckles\Scribe\Tools\MarkdownParser;

class Markdown extends Component
{
    public function render(): Closure
    {
        return fn (array $data) => MarkdownParser::instance()->text($data['slot']);
    }
}
