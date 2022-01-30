{{--
    Copyright (c) ppy Pty Ltd <contact@ppy.sh>. Licensed under the GNU Affero General Public License v3.0.
    See the LICENCE file in the repository root for full licence text.
--}}
@extends('master', [
    'titlePrepend' => osu_trans('records.type.'.str_replace('-', '_', $recordsIndexJson['type'])),
])

@section('content')
    <div class="js-react--records-index"></div>
@endsection

@section('script')
    @parent

    <script id="json-records-index" type="application/json">
        {!! json_encode($recordsIndexJson) !!}
    </script>

    @include('layout._react_js', ['src' => 'js/records-index.js'])
@endsection
