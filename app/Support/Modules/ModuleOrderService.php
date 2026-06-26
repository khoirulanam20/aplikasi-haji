<?php

namespace App\Support\Modules;

use App\Models\Module;
use App\Models\ModuleGroup;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ModuleOrderService
{
    public function __construct(private readonly ModuleRegistryService $registry)
    {
    }

    public function reorderGroups(array $orderedIds): void
    {
        $orderedIds = array_values(array_map('intval', $orderedIds));

        if ($orderedIds === []) {
            return;
        }

        $groups = ModuleGroup::query()->whereIn('id', $orderedIds)->get()->keyBy('id');

        if ($groups->count() !== count($orderedIds)) {
            throw ValidationException::withMessages([
                'ids' => 'Daftar grup modul tidak valid.',
            ]);
        }

        DB::transaction(function () use ($orderedIds, $groups): void {
            foreach ($orderedIds as $index => $id) {
                $groups[$id]->update(['sort_order' => ($index + 1) * 10]);
            }
        });
    }

    public function reorderModules(int $groupId, array $orderedIds): void
    {
        $orderedIds = array_values(array_map('intval', $orderedIds));

        if ($orderedIds === []) {
            return;
        }

        $group = ModuleGroup::query()->findOrFail($groupId);

        $modules = Module::query()
            ->where('module_group_id', $group->id)
            ->whereIn('id', $orderedIds)
            ->get()
            ->keyBy('id');

        if ($modules->count() !== count($orderedIds)) {
            throw ValidationException::withMessages([
                'ids' => 'Daftar modul tidak valid untuk grup ini.',
            ]);
        }

        DB::transaction(function () use ($orderedIds, $modules): void {
            foreach ($orderedIds as $index => $id) {
                $modules[$id]->update(['sort_order' => ($index + 1) * 10]);
            }

            foreach ($modules as $module) {
                $module->refresh();
                $this->registry->syncModule($module);
            }
        });
    }
}
