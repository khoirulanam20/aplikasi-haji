<?php

namespace App\Http\Controllers\Superadmin;

use App\Http\Controllers\Controller;
use App\Models\Module;
use App\Rules\IconoirIconHtml;
use App\Models\ModuleGroup;
use App\Support\Modules\ModuleConventionValidator;
use App\Support\Modules\ModuleRegistryService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class ModuleController extends Controller
{
    public function __construct(
        private readonly ModuleRegistryService $registry,
        private readonly ModuleConventionValidator $conventions,
    ) {
    }

    public function index(): Response
    {
        $groups = ModuleGroup::query()
            ->with(['modules.actions'])
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        return Inertia::render('Admin/Modules/Index', [
            'groups' => $groups,
            'allGroups' => ModuleGroup::query()->where('is_active', true)->orderBy('name')->get(['id', 'name', 'code']),
            'actions' => Module::ACTIONS,
            'layoutTypes' => [
                Module::LAYOUT_TABLE_BASE => 'Table base',
                Module::LAYOUT_FORM_BASE => 'Form base',
            ],
            'actionPresets' => [
                Module::LAYOUT_TABLE_BASE => Module::actionPreset(Module::LAYOUT_TABLE_BASE),
                Module::LAYOUT_FORM_BASE => Module::actionPreset(Module::LAYOUT_FORM_BASE),
            ],
            'iconoirIcons' => config('iconoir.icons', []),
            'reorderUrls' => [
                'groups' => route(admin_route_name('module-groups.reorder')),
                'modules' => route(admin_route_name('modules.reorder')),
            ],
        ]);
    }

    public function show(Module $module): Response
    {
        $module->load(['group', 'actions']);

        return Inertia::render('Admin/Modules/Show', [
            'module' => $module,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $payload = $this->validatedModule($request);

        $enabledActions = array_values(array_unique($payload['enabled_actions'] ?? []));
        $hasNotifications = in_array('notify', $enabledActions, true);
        $payload['enabled_actions'] = $enabledActions;

        $module = Module::create([
            'module_group_id' => $payload['module_group_id'],
            'title' => $payload['title'],
            'code' => $payload['code'],
            'layout_type' => $payload['layout_type'],
            'route_name' => $payload['route_name'] ?? null,
            'icon' => $payload['icon'] ?? null,
            'description' => $payload['description'] ?? null,
            'is_active' => $request->boolean('is_active', true),
            'show_in_sidebar' => $request->boolean('show_in_sidebar'),
            'has_notifications' => $hasNotifications,
            'sort_order' => $payload['sort_order'] ?? ((int) Module::where('module_group_id', $payload['module_group_id'])->max('sort_order') + 10),
        ]);

        $this->registry->syncActions(
            $module,
            $payload['enabled_actions'] ?? [],
            $payload['custom_actions'] ?? [],
        );

        return back()->with('status', 'module-created');
    }

    public function update(Request $request, Module $module): RedirectResponse
    {
        $payload = $this->validatedModule($request, $module);

        $enabledActions = array_values(array_unique($payload['enabled_actions'] ?? []));
        $hasNotifications = in_array('notify', $enabledActions, true);
        $payload['enabled_actions'] = $enabledActions;

        $module->update([
            'module_group_id' => $payload['module_group_id'],
            'title' => $payload['title'],
            'code' => $payload['code'],
            'layout_type' => $payload['layout_type'],
            'route_name' => $payload['route_name'] ?? null,
            'icon' => $payload['icon'] ?? null,
            'description' => $payload['description'] ?? null,
            'is_active' => $request->boolean('is_active'),
            'show_in_sidebar' => $request->boolean('show_in_sidebar'),
            'has_notifications' => $hasNotifications,
            'sort_order' => $payload['sort_order'] ?? 0,
        ]);

        $this->registry->syncActions(
            $module,
            $payload['enabled_actions'] ?? [],
            $payload['custom_actions'] ?? [],
        );

        return back()->with('status', 'module-updated');
    }

    public function destroy(Module $module): RedirectResponse
    {
        $this->registry->removeModule($module);
        $module->delete();

        return back()->with('status', 'module-deleted');
    }

    private function validatedModule(Request $request, ?Module $module = null): array
    {
        $payload = $request->validate([
            'module_group_id' => ['required', 'exists:module_groups,id'],
            'title' => ['required', 'string', 'max:100'],
            'code' => [
                'required',
                'string',
                'max:100',
                'regex:/^[a-z0-9_]+$/',
                Rule::unique('modules', 'code')->ignore($module?->id),
            ],
            'route_name' => ['nullable', 'string', 'max:150'],
            'icon' => ['nullable', 'string', 'max:255', new IconoirIconHtml],
            'description' => ['nullable', 'string', 'max:1000'],
            'layout_type' => ['required', Rule::in(Module::LAYOUT_TYPES)],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'enabled_actions' => ['array'],
            'enabled_actions.*' => ['string', 'regex:/^[a-z0-9_]+$/', 'max:50'],
            'custom_actions' => ['array'],
            'custom_actions.*.action' => [
                'required',
                'string',
                'regex:/^[a-z0-9_]+$/',
                'max:50',
                Rule::notIn(Module::ACTIONS),
            ],
            'custom_actions.*.label' => ['required', 'string', 'max:100'],
        ]);

        $customSlugs = collect($payload['custom_actions'] ?? [])->pluck('action');
        if ($customSlugs->duplicates()->isNotEmpty()) {
            throw ValidationException::withMessages([
                'custom_actions' => 'Judul hak akses kustom tidak boleh duplikat.',
            ]);
        }

        $enabled = collect($payload['enabled_actions'] ?? []);
        $invalidEnabled = $enabled
            ->reject(fn (string $action) => Module::isBuiltInAction($action) || $customSlugs->contains($action));

        if ($invalidEnabled->isNotEmpty()) {
            throw ValidationException::withMessages([
                'enabled_actions' => 'Aksi tidak dikenali: '.$invalidEnabled->implode(', '),
            ]);
        }

        if ($request->boolean('show_in_sidebar')) {
            if (empty($payload['route_name'])) {
                throw ValidationException::withMessages([
                    'route_name' => 'Route name wajib diisi jika modul ditampilkan di sidebar.',
                ]);
            }

            if (! Route::has($payload['route_name'])) {
                throw ValidationException::withMessages([
                    'route_name' => 'Route tidak terdaftar. Cek dengan php artisan route:list.',
                ]);
            }
        }

        $this->conventions->validate([
            'code' => $payload['code'],
            'route_name' => $payload['route_name'] ?? null,
        ]);

        $payload['enabled_actions'] = array_values(array_unique($payload['enabled_actions'] ?? []));

        return $payload;
    }
}
