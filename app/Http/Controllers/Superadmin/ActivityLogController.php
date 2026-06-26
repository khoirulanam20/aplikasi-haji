<?php

namespace App\Http\Controllers\Superadmin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Support\ActivityLog\ActivityLogPresenter;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Spatie\Activitylog\Models\Activity;

class ActivityLogController extends Controller
{
    public function index(Request $request): Response
    {
        $activities = Activity::query()
            ->with(['causer', 'subject'])
            ->when($request->filled('q'), fn ($query) => $query->where('description', 'like', '%'.$request->string('q').'%'))
            ->when($request->filled('causer_id'), fn ($query) => $query->where('causer_id', $request->integer('causer_id')))
            ->when($request->filled('event'), fn ($query) => $query->where('event', $request->string('event')))
            ->when($request->filled('subject_type'), fn ($query) => $query->where('subject_type', $request->string('subject_type')))
            ->when($request->filled('date_from'), fn ($query) => $query->whereDate('created_at', '>=', $request->string('date_from')))
            ->when($request->filled('date_to'), fn ($query) => $query->whereDate('created_at', '<=', $request->string('date_to')))
            ->orderByDesc('id')
            ->paginate($request->integer('per_page', 15))
            ->withQueryString()
            ->through(fn (Activity $activity) => ActivityLogPresenter::toArray($activity));

        return Inertia::render('Admin/ActivityLog/Index', [
            'activities' => $activities,
            'filters' => [
                'q' => $request->string('q')->toString(),
                'causer_id' => $request->string('causer_id')->toString(),
                'event' => $request->string('event')->toString(),
                'subject_type' => $request->string('subject_type')->toString(),
                'date_from' => $request->string('date_from')->toString(),
                'date_to' => $request->string('date_to')->toString(),
            ],
            'filterOptions' => [
                'events' => Activity::query()
                    ->whereNotNull('event')
                    ->distinct()
                    ->orderBy('event')
                    ->pluck('event')
                    ->values()
                    ->all(),
                'subject_types' => Activity::query()
                    ->whereNotNull('subject_type')
                    ->distinct()
                    ->orderBy('subject_type')
                    ->pluck('subject_type')
                    ->map(fn (string $type) => [
                        'value' => $type,
                        'label' => ActivityLogPresenter::subjectTypeLabel($type),
                    ])
                    ->values()
                    ->all(),
                'causers' => User::query()
                    ->whereIn('id', Activity::query()->whereNotNull('causer_id')->distinct()->pluck('causer_id'))
                    ->orderBy('name')
                    ->get(['id', 'name', 'email'])
                    ->map(fn (User $user) => [
                        'id' => $user->id,
                        'name' => $user->name,
                        'email' => $user->email,
                    ])
                    ->values()
                    ->all(),
            ],
        ]);
    }

    public function destroy(Activity $activity): RedirectResponse
    {
        $activity->delete();

        return back()->with('status', 'deleted');
    }
}
