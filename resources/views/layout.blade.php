<!DOCTYPE html>
<html lang="en" class="h-full bg-slate-950">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Dashboard | Laravel Radar</title>
    @vite('resources/js/app.ts', 'vendor/radar')
</head>
<body class="min-h-full bg-slate-950 text-slate-100 antialiased">
    @yield('content')
</body>
</html>
