<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCountryRequest;
use App\Models\Country;
use App\Services\CountryCatalogService;
use Illuminate\Database\QueryException;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Throwable;

class CountryController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(Country::class, 'country');
    }

    public function index(): View
    {
        $countries = Country::query()->orderBy('sort_order')->orderBy('name')->paginate(20);

        return view('settings.countries.index', compact('countries'));
    }

    public function create(CountryCatalogService $catalog): View|RedirectResponse
    {
        try {
            $all = $catalog->listSorted();
        } catch (Throwable $e) {
            report($e);

            return redirect()->route('countries.index')
                ->withErrors(['catalog' => __('Could not load the country list from the internet. Please try again later.')]);
        }

        $existing = Country::query()->pluck('code')->map(fn (string $c): string => strtoupper($c))->all();
        $options = array_values(array_filter($all, fn (array $row): bool => ! in_array($row['code'], $existing, true)));

        if ($options === []) {
            return redirect()->route('countries.index')
                ->with('status', __('Every country from the catalog is already in your list.'));
        }

        return view('settings.countries.create', compact('options'));
    }

    public function store(StoreCountryRequest $request, CountryCatalogService $catalog): RedirectResponse
    {
        $code = $request->validated('code');
        $name = $catalog->nameForCode($code);
        if ($name === null || $name === '') {
            return redirect()->route('countries.create')
                ->withErrors(['code' => __('Invalid country code.')]);
        }

        $sortOrder = $catalog->numericSortOrderForCode($code);

        Country::create([
            'name' => $name,
            'code' => $code,
            'is_active' => true,
            'sort_order' => $sortOrder,
        ]);

        return redirect()->route('countries.index')->with('status', __('Country added.'));
    }

    public function destroy(Country $country): RedirectResponse
    {
        try {
            $country->delete();
        } catch (QueryException $e) {
            return redirect()->route('countries.index')
                ->withErrors(['delete' => __('Cannot delete: applications still reference this country.')]);
        }

        return redirect()->route('countries.index')->with('status', __('Country deleted.'));
    }
}
