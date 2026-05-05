<?php

declare(strict_types=1);

namespace JoshDonnell\Radar\Http\Controllers;

use Illuminate\Contracts\View\View;

final class DashboardController
{
    public function __invoke(): View
    {
        return view()->make('radar::dashboard');
    }
}
