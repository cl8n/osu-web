{{--
    Copyright (c) ppy Pty Ltd <contact@ppy.sh>. Licensed under the GNU Affero General Public License v3.0.
    See the LICENCE file in the repository root for full licence text.
--}}
@php
    use Knuckles\Scribe\Tools\WritingUtils as u;
@endphp
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta content="IE=edge,chrome=1" http-equiv="X-UA-Compatible">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
    <title>{!! $metadata['title'] !!}</title>

    <link href="https://fonts.googleapis.com/css?family=PT+Sans&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="{!! $assetPathPrefix !!}css/theme-default.style.css" media="screen">
    <link rel="stylesheet" href="{!! $assetPathPrefix !!}css/theme-default.print.css" media="print">

    <script src="https://cdn.jsdelivr.net/npm/lodash@4.17.10/lodash.min.js"></script>

    <link rel="stylesheet"
          href="https://unpkg.com/@highlightjs/cdn-assets@10.7.2/styles/obsidian.min.css">
    <script src="https://unpkg.com/@highlightjs/cdn-assets@10.7.2/highlight.min.js"></script>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/jets/0.14.1/jets.min.js"></script>

@if(isset($metadata['example_languages']))
    <style id="language-style">
        /* starts out as display none and is replaced with js later  */
        @foreach($metadata['example_languages'] as $lang)
            body .content .{{ $lang }}-example code { display: none; }
        @endforeach
    </style>
@endif

@if($tryItOut['enabled'] ?? true)
    <script>
        var baseUrl = "{{ $tryItOut['base_url'] ?? config('app.url') }}";
        var useCsrf = Boolean({{ $tryItOut['use_csrf'] ?? null }});
        var csrfUrl = "{{ $tryItOut['csrf_url'] ?? null }}";
    </script>
    <script src="{{ u::getVersionedAsset($assetPathPrefix.'js/tryitout.js') }}"></script>
@endif

    <script src="{{ u::getVersionedAsset($assetPathPrefix.'js/theme-default.js') }}"></script>

    <script src="{{ unmix('js/runtime.js') }}"></script>
    <script src="{{ unmix('js/docs.js') }}"></script>
    <link rel="stylesheet" href="{{ unmix('css/docs.css') }}">
</head>

<body data-languages="{{ json_encode($metadata['example_languages'] ?? []) }}">

@php
    // Move the "Undocumented" endpoint group to the end. Scribe has its own
    // sorting configuration, but it doesn't have a way to specify that some
    // group(s) should come after all the rest.
    usort($groupedEndpoints, fn (array $a, array $b) => match ('Undocumented') {
        $a['name'] => 1,
        $b['name'] => -1,
        default => 0,
    });

    $markdownParser = \Knuckles\Scribe\Tools\MarkdownParser::instance();
@endphp

@prepend('sections')
<div class="page-wrapper">
    <div class="dark-box"></div>
    <div class="content">
        @php $markdownParser->headings = []; @endphp
        @include("docs.intro")
        @php $headingsPrepend = $markdownParser->headings; @endphp

        @include("scribe::themes.default.groups")

        @php $markdownParser->headings = []; @endphp
        @include("docs.outro")
        @php $headingsAppend = $markdownParser->headings; @endphp
    </div>
    <div class="dark-box">
        @if(isset($metadata['example_languages']))
            <div class="lang-selector">
                @foreach($metadata['example_languages'] as $name => $lang)
                    @php if (is_numeric($name)) $name = $lang; @endphp
                    <button type="button" class="lang-button" data-language-name="{{$lang}}">{{$name}}</button>
                @endforeach
            </div>
        @endif
    </div>
</div>
@endprepend

@php
    use App\Libraries\ApidocRouteHelper;
    use Illuminate\Support\Collection;
    use Knuckles\Camel\Output\OutputEndpointData;

    /**
     * Convert headings from Scribe's markdown parser into a format usable in
     * the sidebar view.
     *
     * Mostly copied from {@see \Knuckles\Scribe\Writing\HtmlWriter::getHeadings()}.
     */
    function format_markdown_headings(array $headings): array
    {
        $formattedHeadings = [];

        $lastL1ElementIndex = null;
        foreach ($headings as $heading) {
            $element = [
                'slug' => $heading['slug'],
                'name' => $heading['text'],
                'subheadings' => [],
            ];;
            if ($heading['level'] === 1) {
                $formattedHeadings[] = $element;
                $lastL1ElementIndex = count($formattedHeadings) - 1;
            } elseif ($heading['level'] === 2 && !is_null($lastL1ElementIndex)) {
                $formattedHeadings[$lastL1ElementIndex]['subheadings'][] = $element;
            }
        }

        return $formattedHeadings;
    }

    /**
     * Get headings from grouped endpoints.
     *
     * Mostly copied from {@see \Knuckles\Scribe\Writing\HtmlWriter::getHeadings()}.
     */
    function get_endpoint_headings(array $groupedEndpoints): array
    {
        $getEndpointHeading = function (OutputEndpointData $endpoint) {
            $title = ApidocRouteHelper::getTitle($endpoint);

            return [
                'slug' => Str::slug($title),
                'name' => $title,
                'subheadings' => [],
            ];
        };

        $headings = array_map(function (array $group) use ($getEndpointHeading) {
            $groupSlug = Str::slug($group['name']);

            return [
                'slug' => $groupSlug,
                'name' => $group['name'],
                'subheadings' => collect($group['subgroups'])
                    ->flatMap(function (Collection $endpoints, string $subgroupName) use ($getEndpointHeading, $groupSlug) {
                        if ($subgroupName === "") {
                            return $endpoints->map($getEndpointHeading)->all();
                        }

                        return [[
                            'slug' => "${groupSlug}-".Str::slug($subgroupName),
                            'name' => $subgroupName,
                            'subheadings' => $endpoints->map($getEndpointHeading)->all(),
                        ]];
                    })
                    ->all(),
            ];
        }, $groupedEndpoints);

        return $headings;
    }

    // Replace the TOC headings generated by Scribe. Our version has different
    // IDs and names, and also includes headings generated by our "intro" and
    // "outro" views.
    $headings = array_merge(
        format_markdown_headings($headingsPrepend),
        get_endpoint_headings($groupedEndpoints),
        format_markdown_headings($headingsAppend),
    );

    // Add osu! links to the bottom of the sidebar.
    array_unshift(
        $metadata['links'],
        '<a href="https://github.com/ppy/osu-web">osu-web on GitHub</a>',
        '<a href="'.config('app.url').'">osu!</a>',
    );
@endphp

@prepend('sections')
@include("scribe::themes.default.sidebar")
@endprepend

@stack('sections')

</body>
</html>
