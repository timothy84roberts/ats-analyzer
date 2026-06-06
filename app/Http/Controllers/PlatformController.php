<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePlatformRequest;
use App\Http\Requests\UpdatePlatformRequest;
use App\Models\Platform;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class PlatformController extends Controller
{
    public function index(): View
    {
        $platforms = Platform::query()->orderBy('sort_order')->orderBy('name')->get();

        return view('settings.platforms.index', compact('platforms'));
    }

    public function reorder(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'order' => ['required', 'array', 'min:1'],
            'order.*' => ['required', 'integer', 'distinct', 'exists:platforms,id'],
        ]);

        $ids = array_map('intval', $validated['order']);

        DB::transaction(function () use ($ids): void {
            foreach ($ids as $index => $id) {
                Platform::query()->whereKey($id)->update(['sort_order' => ($index + 1) * 10]);
            }
        });

        return response()->json(['status' => 'ok']);
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
        $nextSortOrder = ((int) Platform::query()->max('sort_order')) + 10;
        $platform = Platform::create($data);
        $platform->forceFill(['sort_order' => $nextSortOrder])->save();

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
