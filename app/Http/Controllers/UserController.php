<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreManagedUserRequest;
use App\Http\Requests\UpdateManagedUserRequest;
use App\Models\User;
use App\Services\CountryCatalogService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\View\View;

class UserController extends Controller
{
    public function index(CountryCatalogService $catalog): View
    {
        $users = User::query()
            ->where('is_admin', false)
            ->orderBy('name')
            ->paginate(20);

        $countryNames = collect($catalog->listSorted())
            ->mapWithKeys(fn (array $row) => [$row['code'] => $row['name']]);

        return view('users.index', compact('users', 'countryNames'));
    }

    public function create(CountryCatalogService $catalog): View
    {
        return view('users.create', $this->formOptions($catalog));
    }

    public function store(StoreManagedUserRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $data['password'] = Hash::make(Str::password(32));
        $data['is_admin'] = false;

        User::create($data);

        return redirect()->route('users.index')->with('status', __('User created.'));
    }

    public function edit(User $user, CountryCatalogService $catalog): View
    {
        $this->ensureManagedUser($user);

        return view('users.edit', array_merge(
            ['user' => $user],
            $this->formOptions($catalog)
        ));
    }

    public function update(UpdateManagedUserRequest $request, User $user): RedirectResponse
    {
        $this->ensureManagedUser($user);

        $user->update($request->validated());

        return redirect()->route('users.index')->with('status', __('User updated.'));
    }

    public function destroy(User $user): RedirectResponse
    {
        $this->ensureManagedUser($user);

        if ($user->jobApplications()->exists() || $user->jobApplicationCalls()->exists()) {
            return redirect()->route('users.index')
                ->withErrors(['delete' => __('Cannot delete: this user still has job applications or schedule items.')]);
        }

        $user->delete();

        return redirect()->route('users.index')->with('status', __('User deleted.'));
    }

    /**
     * @return array<string, mixed>
     */
    private function formOptions(CountryCatalogService $catalog): array
    {
        $countryOptions = collect($catalog->listSorted())
            ->map(fn (array $row) => [
                'id' => $row['code'],
                'name' => $row['name'],
                'code' => $row['code'],
                'flag' => 'https://flagcdn.com/24x18/'.strtolower($row['code']).'.png',
            ])
            ->values();

        return [
            'countryOptions' => $countryOptions,
        ];
    }

    private function ensureManagedUser(User $user): void
    {
        abort_if($user->is_admin, 404);
    }
}
