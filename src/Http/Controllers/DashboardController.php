<?php

declare(strict_types=1);

namespace JoshDonnell\Radar\Http\Controllers;

use Illuminate\Contracts\View\View;
use JoshDonnell\Radar\Support\Assets;

final class DashboardController
{
    public function __invoke(): View
    {
        return view()->make('radar::dashboard', [
            'assetsAreCurrent' => Assets::areCurrent(),
        ]);
    }
}
