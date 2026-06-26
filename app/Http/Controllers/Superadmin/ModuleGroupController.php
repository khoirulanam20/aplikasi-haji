<?php

namespace App\Http\Controllers\Superadmin;

use App\Http\Controllers\Controller;
use App\Models\ModuleGroup;
use App\Support\Modules\ModuleRegistryService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Validation\Rule;

class ModuleGroupController extends Controller
{
    public function __construct(private readonly ModuleRegistryService $registry)
    {
    }

    public function store(Request $request): RedirectResponse
    {
        $payload = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'code' => ['required', 'string', 'max:100', 'regex:/^[a-z0-9_]+$/', 'unique:module_groups,code'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        ModuleGroup::create([
            'name' => $payload['name'],
            'code' => $payload['code'],
            'sort_order' => $payload['sort_order'] ?? ((int) ModuleGroup::max('sort_order') + 10),
            'is_active' => $request->boolean('is_active', true),
        ]);

        Cache::forever('menu_sidebars', null);

        return back()->with('status', 'group-created');
    }

    public function update(Request $request, ModuleGroup $moduleGroup): RedirectResponse
    {
        $payload = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'code' => ['required', 'string', 'max:100', 'regex:/^[a-z0-9_]+$/', Rule::unique('module_groups', 'code')->ignore($moduleGroup->id)],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $moduleGroup->update([
            'name' => $payload['name'],
            'code' => $payload['code'],
            'sort_order' => $payload['sort_order'] ?? 0,
            'is_active' => $request->boolean('is_active'),
        ]);

        Cache::forever('menu_sidebars', null);

        return back()->with('status', 'group-updated');
    }

    public function destroy(ModuleGroup $moduleGroup): RedirectResponse
    {
        $moduleGroup->load(['modules.actions']);

        foreach ($moduleGroup->modules as $module) {
            $this->registry->removeModule($module);
        }

        $moduleGroup->delete();

        Cache::forever('menu_sidebars', null);

        return back()->with('status', 'group-deleted');
    }
}
