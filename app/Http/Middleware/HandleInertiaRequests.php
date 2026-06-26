<?php

namespace App\Http\Middleware;

use App\Support\Inertia\SharedDataProvider;
use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    protected $rootView = 'app';

    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        $shared = app(SharedDataProvider::class);
        $domain = $shared->navigationDomain();
        $user = $request->user();
        $user?->load('roles');

        return [
            ...parent::share($request),
            'auth' => [
                'user' => $user ? [
                    'id' => $user->id,
                    'name' => $user->name,
                    'username' => $user->username,
                    'email' => $user->email,
                    'avatar_url' => $user->avatarUrl(),
                    'avatar_initial' => $user->avatarInitial(),
                    'roles' => $user->roles->pluck('name')->values()->all(),
                    'completed_tours' => ($user->preferences ?? [])['tours'] ?? [],
                ] : null,
                'permissions' => $user?->getAllPermissions()->pluck('name')->values()->all() ?? [],
            ],
            'app' => [
                'name' => $shared->appDisplayName(),
                'theme' => $shared->themeColors(),
                'logoUrl' => $shared->webLogoUrl(),
                'faviconUrl' => $shared->webFaviconUrl(),
                'googleOAuthEnabled' => $shared->googleOAuthEnabled(),
            ],
            'sidebarNavigation' => $shared->sidebarNavigation($domain),
            'dynamicMenus' => $shared->dynamicMenus($domain),
            'impersonating' => $request->session()->has('impersonator_id'),
            'flash' => $shared->flashMessage(),
            'hajjFlash' => [
                'generated_password' => $request->session()->pull('generated_password'),
                'generated_username' => $request->session()->pull('generated_username'),
                'generated_email' => $request->session()->pull('generated_email'),
                'import_summary' => $request->session()->pull('import_summary'),
                'import_preview' => $request->session()->pull('import_preview'),
                'import_job_token' => $request->session()->pull('import_job_token'),
            ],
        ];
    }
}
