<!DOCTYPE html>
<html lang="en" class="h-full bg-bg">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="color-scheme" content="dark light">
    <title>Radar | {{ config('app.name') }}</title>
    <link rel="icon" type="image/svg+xml" href="data:image/svg+xml,{{ rawurlencode('<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="#7fd8a6" stroke-width="2.2"><circle cx="12" cy="12" r="9"/><circle cx="12" cy="12" r="4.5"/><circle cx="12" cy="12" r="1" fill="#7fd8a6"/></svg>') }}">
    @vite('resources/js/app.ts', 'vendor/radar')
</head>
<body class="min-h-full bg-bg text-fg antialiased">
    @yield('content')
</body>
</html>
