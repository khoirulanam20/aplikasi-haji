<?php

namespace App\Http\Controllers\Superadmin;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Support\Modules\PermissionGroupBuilder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use App\Http\Requests\Superadmin\StoreRoleRequest;
use App\Http\Requests\Superadmin\UpdateRoleRequest;
use App\Support\Navigation\SidebarMenuCacheInvalidator;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;
use Spatie\Permission\PermissionRegistrar;

class RolePermissionController extends Controller
{
    public function __construct(private readonly SidebarMenuCacheInvalidator $menuCache)
    {
    }

    public function index(Request $request): Response
    {
        $roles = Role::query()
            ->with('permissions')
            ->orderBy('title')
            ->orderBy('name')
            ->paginate($request->integer('per_page', 10))
            ->withQueryString();

        $selectedRoleId = $request->integer('role_id') ?: Role::query()->orderBy('title')->orderBy('name')->value('id');

        return Inertia::render('Admin/RolePermission/Index', [
            'roles' => $roles,
            'permissionGroups' => app(PermissionGroupBuilder::class)->build(),
            'filters' => ['role_id' => $selectedRoleId],
        ]);
    }

    public function storeRole(StoreRoleRequest $request): RedirectResponse
    {
        $payload = $request->validated();

        $this->menuCache->invalidate();

        Role::create([
            'name' => $payload['code'],
            'guard_name' => 'web',
            'title' => $payload['title'],
            'description' => $payload['description'] ?? null,
            'is_active' => $request->boolean('is_active', true),
        ]);

        return redirect()
            ->route(admin_route_name('role-permission.index'), ['role_id' => Role::where('name', $payload['code'])->value('id')])
            ->with('status', 'role-created');
    }

    public function updateRole(UpdateRoleRequest $request, Role $role): RedirectResponse
    {
        $payload = $request->validated();

        $role->update([
            'name' => $payload['code'],
            'title' => $payload['title'],
            'description' => $payload['description'] ?? null,
            'is_active' => $request->boolean('is_active'),
        ]);

        return redirect()->route(admin_route_name('role-permission.index'), ['role_id' => $role->id])
            ->with('status', 'role-updated');
    }

    public function destroyRole(Role $role): RedirectResponse
    {
        if ($role->name === 'superadmin') {
            return back()->withErrors(['role' => 'Role sistem tidak bisa dihapus.']);
        }

        $role->delete();

        return redirect()->route(admin_route_name('role-permission.index'))->with('status', 'role-deleted');
    }

    public function updatePermissions(Request $request, Role $role): RedirectResponse
    {
        $payload = $request->validate([
            'permissions' => ['nullable', 'array'],
            'permissions.*' => [
                'string',
                Rule::exists('permissions', 'name')->where('guard_name', 'web'),
            ],
        ]);

        if ($role->name === 'superadmin') {
            return back()->withErrors(['permissions' => 'Permission role superadmin tidak bisa diubah dari UI.']);
        }

        $role->syncPermissions($payload['permissions'] ?? []);

        app(PermissionRegistrar::class)->forgetCachedPermissions();
        $this->menuCache->invalidate();

        return redirect()->route(admin_route_name('role-permission.index'), ['role_id' => $role->id])
            ->with('status', 'permissions-updated');
    }
}
