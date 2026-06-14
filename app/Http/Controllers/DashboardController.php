<?php

namespace App\Http\Controllers;

use App\Models\JobApplication;
use App\Services\DashboardService;
use App\Models\Country;
use App\Models\Platform;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(Request $request, DashboardService $dashboard): View
    {
        $input = $request->only(['period', 'offset', 'country_id', 'platform_id', 'outcome_status']);
        $data = $dashboard->build($request->user(), $input);

        return view('dashboard', array_merge($data, [
            'filterCountries' => Country::query()->orderBy('sort_order')->orderBy('name')->get(),
            'filterPlatforms' => Platform::query()->orderBy('sort_order')->orderBy('name')->get(),
            'outcomeOptions' => JobApplication::outcomeStatuses(),
        ]));
    }
}
