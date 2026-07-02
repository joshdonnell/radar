@extends('radar::layout')

@section('content')
    <script>
        window.radar = {
            csrfToken: @js(csrf_token()),
            latestScanUrl: @js(route('radar.api.scans.latest', [], false)),
            scanUrl: @js(route('radar.api.scans.run', [], false)),
        };
    </script>

    @unless ($assetsAreCurrent ?? true)
        <div style="position:sticky;top:0;z-index:50;display:flex;flex-wrap:wrap;align-items:center;justify-content:center;gap:6px;background:#e6b256;color:#0a0a0b;font-family:system-ui,-apple-system,'Segoe UI',Roboto,Helvetica,Arial,sans-serif;font-size:13px;font-weight:500;line-height:1.4;padding:10px 16px;text-align:center;">
            <span>Radar's dashboard assets are out of date after upgrading.</span>
            <span>Run</span>
            <code style="font-family:ui-monospace,'SFMono-Regular',Menlo,Consolas,monospace;background:rgba(10,10,11,0.14);padding:2px 7px;border-radius:5px;">php artisan radar:upgrade</code>
            <span>to update them.</span>
        </div>
    @endunless

    <div id="radar" class="bg-bg text-fg font-sans"></div>
@endsection
