<?php

namespace App\Http\Controllers;

use App\Models\Country;
use App\Models\Platform;
use Illuminate\View\View;

class SettingsController extends Controller
{
    public function __invoke(): View
    {
        return view('settings.index', [
            'reference' => [
                'country_count' => Country::query()->count(),
                'platform_count' => Platform::query()->count(),
            ],
        ]);
    }
}
