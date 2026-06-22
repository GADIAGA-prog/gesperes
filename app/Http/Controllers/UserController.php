<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Models\Structure;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    public function index(): View
    {
        $this->authorize('viewAny', User::class);

        $users = User::with('roles')->orderBy('name')->paginate(20);

        return view('users.index', ['users' => $users]);
    }

    public function create(): View
    {
        $this->authorize('create', User::class);
        return view('users.create', $this->referentiels());
    }

    public function store(StoreUserRequest $request): RedirectResponse
    {
        $data = $request->validated();

        $user = User::create([
            'name'         => $data['name'],
            'email'        => $data['email'],
            'password'     => Hash::make($data['password']),
            'actif'        => $data['actif'] ?? true,
            'region'       => $data['region'] ?? null,
            'structure_id' => $data['structure_id'] ?? null,
        ]);

        $user->syncRoles($data['roles'] ?? []);

        return redirect()->route('users.index')->with('success', "Utilisateur « {$user->name} » créé.");
    }

    public function edit(User $user): View
    {
        $this->authorize('update', $user);
        return view('users.edit', array_merge(['user' => $user], $this->referentiels()));
    }

    public function update(UpdateUserRequest $request, User $user): RedirectResponse
    {
        $data = $request->validated();

        $user->update([
            'name'         => $data['name'],
            'email'        => $data['email'],
            'actif'        => $data['actif'] ?? false,
            'region'       => $data['region'] ?? null,
            'structure_id' => $data['structure_id'] ?? null,
        ]);

        if (! empty($data['password'])) {
            $user->update(['password' => Hash::make($data['password'])]);
        }

        $user->syncRoles($data['roles'] ?? []);

        return redirect()->route('users.index')->with('success', "Utilisateur « {$user->name} » mis à jour.");
    }

    public function destroy(User $user): RedirectResponse
    {
        $this->authorize('delete', $user);
        $nom = $user->name;
        $user->delete();

        return redirect()->route('users.index')->with('success', "Utilisateur « {$nom} » supprimé.");
    }

    private function referentiels(): array
    {
        return [
            'roles'      => Role::orderBy('name')->get(),
            'structures' => Structure::orderBy('libelle')->pluck('libelle', 'id'),
        ];
    }
}
