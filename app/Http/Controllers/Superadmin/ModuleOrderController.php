<?php

namespace App\Http\Controllers\Superadmin;

use App\Http\Controllers\Controller;
use App\Support\Modules\ModuleOrderService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class ModuleOrderController extends Controller
{
    public function __construct(private readonly ModuleOrderService $orderService)
    {
    }

    public function reorderGroups(Request $request): JsonResponse
    {
        $payload = $request->validate([
            'ids' => ['required', 'array', 'min:1'],
            'ids.*' => ['integer', 'distinct'],
        ]);

        $this->orderService->reorderGroups($payload['ids']);

        Cache::forever('menu_sidebars', null);

        return response()->json([
            'ok' => true,
            'message' => 'Urutan grup modul berhasil disimpan.',
        ]);
    }

    public function reorderModules(Request $request): JsonResponse
    {
        $payload = $request->validate([
            'module_group_id' => ['required', 'integer', 'exists:module_groups,id'],
            'ids' => ['required', 'array', 'min:1'],
            'ids.*' => ['integer', 'distinct'],
        ]);

        $this->orderService->reorderModules(
            (int) $payload['module_group_id'],
            $payload['ids']
        );

        Cache::forever('menu_sidebars', null);

        return response()->json([
            'ok' => true,
            'message' => 'Urutan modul berhasil disimpan.',
        ]);
    }
}
