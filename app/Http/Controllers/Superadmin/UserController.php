<?php

namespace App\Http\Controllers\Superadmin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Superadmin\StoreUserRequest;
use App\Http\Requests\Superadmin\UpdateUserRequest;
use App\Models\Role;
use App\Models\User;
use App\Support\Navigation\SidebarMenuCacheInvalidator;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;
use Inertia\Response;

class UserController extends Controller
{
    public function __construct(private readonly SidebarMenuCacheInvalidator $menuCache) {}

    public function index(Request $request): Response
    {
        $users = User::query()
            ->with('roles')
            ->when($request->filled('q'), function ($query) use ($request) {
                $q = '%'.$request->string('q').'%';
                $query->where(function ($builder) use ($q) {
                    $builder->where('name', 'like', $q)
                        ->orWhere('email', 'like', $q)
                        ->orWhere('username', 'like', $q);
                });
            })
            ->when($request->filled('role'), fn ($query) => $query->whereHas(
                'roles',
                fn ($roleQuery) => $roleQuery->where('name', $request->string('role'))
            ))
            ->when($request->filled('status'), function ($query) use ($request) {
                match ($request->string('status')->toString()) {
                    'active' => $query->where('is_active', true),
                    'inactive' => $query->where('is_active', false),
                    default => null,
                };
            })
            ->orderByDesc('id')
            ->paginate($request->integer('per_page', 10))
            ->withQueryString();

        return Inertia::render('Admin/Users/Index', [
            'users' => $users,
            'roles' => Role::query()->where('is_active', true)->orderBy('title')->get(['id', 'name', 'title']),
            'filters' => [
                'q' => $request->string('q')->toString(),
                'role' => $request->string('role')->toString(),
                'status' => $request->string('status')->toString(),
            ],
        ]);
    }

    public function store(StoreUserRequest $request): RedirectResponse
    {
        $payload = $request->validated();

        $user = User::create([
            'name' => $payload['name'],
            'username' => $payload['username'] ?? null,
            'email' => $payload['email'],
            'password' => Hash::make($payload['password']),
            'is_active' => $request->boolean('is_active', true),
        ]);

        $user->syncRoles([$payload['role']]);

        $this->menuCache->invalidate();

        return back()->with('status', 'user-created');
    }

    public function update(UpdateUserRequest $request, User $user): RedirectResponse
    {
        $payload = $request->validated();

        $user->fill([
            'name' => $payload['name'],
            'username' => $payload['username'] ?? null,
            'email' => $payload['email'],
            'is_active' => $request->boolean('is_active'),
        ]);

        if (! empty($payload['password'])) {
            $user->password = Hash::make($payload['password']);
        }

        $user->save();
        $user->syncRoles([$payload['role']]);

        $this->menuCache->invalidate();

        return back()->with('status', 'user-updated');
    }

    public function destroy(User $user): RedirectResponse
    {
        if ($user->id === auth()->id()) {
            return back()->withErrors(['user' => 'Tidak bisa menghapus akun sendiri.']);
        }

        $user->delete();

        $this->menuCache->invalidate();

        return back()->with('status', 'user-deleted');
    }
}
