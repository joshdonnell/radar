@extends('radar::layout')

@section('content')
    <script>
        window.radar = {
            csrfToken: @js(csrf_token()),
            latestScanUrl: @js(route('radar.api.scans.latest', [], false)),
            scanUrl: @js(route('radar.api.scans.run', [], false)),
        };
    </script>

    <div id="radar"></div>
@endsection
