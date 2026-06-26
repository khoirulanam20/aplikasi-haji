<?php

namespace App\Http\Controllers\Superadmin;

use App\Http\Controllers\Controller;
use App\Models\WebSetting;
use App\Support\Media\ImageUploadService;
use App\Support\Modules\FormModuleAccess;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Inertia\Inertia;
use Inertia\Response;

class WebSettingController extends Controller
{
    public function __construct(
        private readonly ImageUploadService $imageUpload,
        private readonly FormModuleAccess $formAccess,
    ) {
    }

    public function index(): Response
    {
        $user = auth()->user();
        $setting = WebSetting::query()->first();

        return Inertia::render('Admin/WebSetting/Index', [
            'setting' => $setting ? [
                'app_name' => $setting->app_name,
                'app_tagline' => $setting->app_tagline,
                'site_description' => $setting->site_description,
                'primary_color' => $setting->primary_color,
                'secondary_color' => $setting->secondary_color,
                'logo_url' => $setting->logoUrl(),
                'favicon_url' => $setting->faviconUrl(),
            ] : null,
            'canSave' => $this->formAccess->canSave('websetting', $user),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $setting = WebSetting::query()->first();

        $payload = $request->validate([
            'app_name' => ['required', 'string', 'max:100'],
            'app_tagline' => ['nullable', 'string', 'max:150'],
            'logo' => ['nullable', 'image', 'max:5120'],
            'favicon' => ['nullable', 'image', 'max:2048'],
            'remove_logo' => ['nullable', 'boolean'],
            'remove_favicon' => ['nullable', 'boolean'],
            'site_description' => ['nullable', 'string', 'max:500'],
            'primary_color' => ['required', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'secondary_color' => ['required', 'regex:/^#[0-9A-Fa-f]{6}$/'],
        ]);

        $data = [
            'app_name' => $payload['app_name'],
            'app_tagline' => $payload['app_tagline'] ?? null,
            'site_description' => $payload['site_description'] ?? null,
            'primary_color' => strtoupper($payload['primary_color']),
            'secondary_color' => strtoupper($payload['secondary_color']),
        ];

        if ($request->boolean('remove_logo')) {
            $this->imageUpload->deleteIfExists($setting?->logo_path);
            $data['logo_path'] = null;
        }

        if ($request->boolean('remove_favicon')) {
            $this->imageUpload->deleteIfExists($setting?->favicon_path);
            $data['favicon_path'] = null;
        }

        if ($request->hasFile('logo')) {
            $data['logo_path'] = $this->imageUpload->store(
                $request->file('logo'),
                'uploads/web-settings',
                [
                    'max_width' => 512,
                    'max_height' => 512,
                    'quality' => 80,
                    'old_path' => $setting?->logo_path,
                ],
            );
        }

        if ($request->hasFile('favicon')) {
            $data['favicon_path'] = $this->imageUpload->store(
                $request->file('favicon'),
                'uploads/web-settings',
                [
                    'max_width' => 192,
                    'max_height' => 192,
                    'quality' => 80,
                    'old_path' => $setting?->favicon_path,
                ],
            );
        }

        WebSetting::query()->updateOrCreate(['id' => 1], $data);

        Cache::forget('web_setting_app_name');
        Cache::forget('web_setting_logo_url');
        Cache::forget('web_setting_favicon_url');
        Cache::forget('web_setting_theme');
        Cache::forget('web_settings_table_exists');

        return back()->with('status', 'web-setting-updated');
    }
}
