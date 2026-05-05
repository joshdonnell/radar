<!DOCTYPE html>
<html lang="en" class="h-full bg-slate-950">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Laravel Radar')</title>
    <link rel="stylesheet" href="{{ asset('vendor/radar/radar.css') }}">
</head>
<body class="min-h-full bg-slate-950 text-slate-100 antialiased">
    @yield('content')

    <script type="module" src="{{ asset('vendor/radar/radar.js') }}"></script>
</body>
</html>
