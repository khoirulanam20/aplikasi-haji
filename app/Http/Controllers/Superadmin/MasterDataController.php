<?php

namespace App\Http\Controllers\Superadmin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Superadmin\StoreMasterDataRequest;
use App\Models\MasterData;
use App\Support\Modules\ModuleNotification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class MasterDataController extends Controller
{
    public function index(Request $request): Response
    {
        $items = MasterData::query()
            ->when($request->filled('q'), fn ($query) => $query->where('name', 'like', '%'.$request->string('q').'%'))
            ->orderByDesc('id')
            ->paginate($request->integer('per_page', 10))
            ->withQueryString();

        return Inertia::render('Admin/MasterData/Index', [
            'items' => $items,
            'filters' => ['q' => $request->string('q')->toString()],
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function store(StoreMasterDataRequest $request): RedirectResponse
    {
        $payload = $request->validated();

        $record = MasterData::create($payload + ['created_by' => auth()->id()]);

        if ($user = $request->user()) {
            ModuleNotification::sendIfAllowed($user, 'masterdata', 'Master Data dibuat', $record->name);
        }

        return back()->with('status', 'created');
    }

    /**
     * Display the specified resource.
     */
    public function update(StoreMasterDataRequest $request, MasterData $masterDatum): RedirectResponse
    {
        $payload = $request->validated();

        $masterDatum->update($payload);

        if ($user = $request->user()) {
            ModuleNotification::sendIfAllowed($user, 'masterdata', 'Master Data diubah', $masterDatum->name);
        }

        return back()->with('status', 'updated');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(MasterData $masterDatum): RedirectResponse
    {
        $name = $masterDatum->name;
        $masterDatum->delete();

        if ($user = auth()->user()) {
            ModuleNotification::sendIfAllowed($user, 'masterdata', 'Master Data dihapus', $name);
        }

        return back()->with('status', 'deleted');
    }
}
