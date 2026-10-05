@extends('radar::layout')

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

    @unless($assetsAreCurrent ?? true)
        {{-- Styled here rather than in the published stylesheet, because that stylesheet is the thing that is out of date. --}}
        <style>
            .radar-assets-banner {
                position: sticky;
                top: 0;
                z-index: 50;
                display: flex;
                flex-wrap: wrap;
                align-items: center;
                justify-content: center;
                gap: 6px;
                padding: 10px 16px;
                background: #e6b256;
                color: #0a0a0b;
                font: 500 13px/1.4 system-ui, -apple-system, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
                text-align: center;
            }

            .radar-assets-banner code {
                padding: 2px 7px;
                border-radius: 5px;
                background: rgba(10, 10, 11, 0.14);
                font-family: ui-monospace, 'SFMono-Regular', Menlo, Consolas, monospace;
            }
        </style>

        <div class="radar-assets-banner" role="alert">
            <span>Radar's dashboard assets are out of date after upgrading.</span>
            <span>Run</span>
            <code>php artisan radar:upgrade</code>
            <span>to update them.</span>
        </div>
    @endunless

    <div id="radar" class="bg-bg text-fg font-sans"></div>
@endsection
