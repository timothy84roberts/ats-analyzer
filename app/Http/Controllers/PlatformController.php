<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePlatformRequest;
use App\Http\Requests\UpdatePlatformRequest;
use App\Models\Platform;
use Illuminate\Database\QueryException;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class PlatformController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(Platform::class, 'platform');
    }

    public function index(): View
    {
        $platforms = Platform::query()->orderBy('sort_order')->orderBy('name')->paginate(20);

        return view('settings.platforms.index', compact('platforms'));
    }

    public function create(): View
    {
        return view('settings.platforms.create');
    }

    public function store(StorePlatformRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $data['is_active'] = $request->boolean('is_active');
        unset($data['sort_order']);
        $platform = Platform::create($data);
        $platform->forceFill(['sort_order' => $platform->id])->save();

        return redirect()->route('platforms.index')->with('status', 'Platform created.');
    }

    public function edit(Platform $platform): View
    {
        return view('settings.platforms.edit', compact('platform'));
    }

    public function update(UpdatePlatformRequest $request, Platform $platform): RedirectResponse
    {
        $data = $request->validated();
        $data['is_active'] = $request->boolean('is_active');
        $data['sort_order'] = $platform->id;
        $platform->update($data);

        return redirect()->route('platforms.index')->with('status', 'Platform updated.');
    }

    public function destroy(Platform $platform): RedirectResponse
    {
        try {
            $platform->delete();
        } catch (QueryException $e) {
            return redirect()->route('platforms.index')
                ->withErrors(['delete' => 'Cannot delete: applications still reference this platform. Reassign or deactivate instead.']);
        }

        return redirect()->route('platforms.index')->with('status', 'Platform deleted.');
    }
}
