<?php

namespace App\Http\Controllers\Superadmin;

use App\Http\Controllers\Controller;
use App\Models\MasterData;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Admin/Dashboard', [
            'stats' => [
                'users' => \App\Models\User::count(),
                'roles' => \Spatie\Permission\Models\Role::count(),
                'master_data' => MasterData::count(),
            ],
            'laravelVersion' => app()->version(),
            'phpVersion' => PHP_VERSION,
        ]);
    }
}
