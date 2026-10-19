@extends('radar::app')

@section('content')
    <script>
        window.radar = {
            csrfToken: @js(csrf_token()),
            latestScanUrl: @js(route('radar.api.scans.latest', [], false)),
            scanUrl: @js(route('radar.api.scans.run', [], false)),
            appName: @js(config('app.name')),
            environment: @js(app()->environment()),
        };
    </script>

    <div id="radar" class="bg-bg text-fg font-sans"></div>
@endsection
