<?php

namespace App\Support\Inertia;

use App\Models\Menu;
use App\Models\WebSetting;
use App\Support\Auth\UserNavigationDomain;
use App\Support\Navigation\MenuBuilder;
use App\Support\Settings\IntegrationConfigService;
use App\Support\Settings\ThemeSettingsService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;

class SharedDataProvider
{
    private bool $webSettingsTableChecked = false;

    private bool $webSettingsTableExists = false;

    public function appDisplayName(): string
    {
        if (! $this->webSettingsTableExists()) {
            return config('app.name', 'Starterkit');
        }

        return Cache::rememberForever('web_setting_app_name', function () {
            return WebSetting::query()->value('app_name') ?: config('app.name', 'Starterkit');
        });
    }

    /**
     * @return array<string, string>
     */
    public function themeColors(): array
    {
        if (! $this->webSettingsTableExists()) {
            return app(ThemeSettingsService::class)->cssVariables(null);
        }

        try {
            return Cache::rememberForever('web_setting_theme', function () {
                $setting = WebSetting::query()->first();

                return app(ThemeSettingsService::class)->cssVariables($setting);
            });
        } catch (\Throwable) {
            return app(ThemeSettingsService::class)->cssVariables(null);
        }
    }

    public function webLogoUrl(): ?string
    {
        if (! $this->webSettingsTableExists()) {
            return null;
        }

        return Cache::rememberForever('web_setting_logo_url', function () {
            return WebSetting::query()->first()?->logoUrl();
        });
    }

    public function webFaviconUrl(): ?string
    {
        if (! $this->webSettingsTableExists()) {
            return null;
        }

        return Cache::rememberForever('web_setting_favicon_url', function () {
            return WebSetting::query()->first()?->faviconUrl();
        });
    }

    public function googleOAuthEnabled(): bool
    {
        return app(IntegrationConfigService::class)->googleOAuthEnabled();
    }

    /**
     * @return array{groups: array<int, array{name: string, sort_order: int, menus: array<int, array<string, mixed>}>, ungrouped: array<int, array<string, mixed>>}
     */
    public function sidebarNavigation(?string $domain): array
    {
        if (! auth()->check()) {
            return ['groups' => [], 'ungrouped' => []];
        }

        $navigation = $this->cachedMenuForUser(
            fn () => app(MenuBuilder::class)->groupedForUserDomain($domain),
        );

        return [
            'groups' => $navigation['groups']->map(fn (array $group) => [
                'name' => $group['name'],
                'sort_order' => $group['sort_order'],
                'menus' => $group['menus']->map(fn (Menu $menu) => $this->serializeMenu($menu))->values()->all(),
            ])->values()->all(),
            'ungrouped' => $navigation['ungrouped']->map(fn (Menu $menu) => $this->serializeMenu($menu))->values()->all(),
        ];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function dynamicMenus(?string $domain): array
    {
        if (! auth()->check()) {
            return [];
        }

        return $this->cachedMenuForUser(
            fn () => app(MenuBuilder::class)->forUserDomain($domain),
        )->map(fn (Menu $menu) => $this->serializeMenu($menu))->values()->all();
    }

    /**
     * @return array<string, mixed>|null
     */
    public function flashMessage(): ?array
    {
        $status = session('status');

        if (! is_string($status) || $status === '') {
            return null;
        }

        $config = config("flash_messages.{$status}");

        if (! is_array($config)) {
            return null;
        }

        return [
            'key' => $status,
            'type' => $config['type'] ?? 'info',
            'title' => $config['title'] ?? 'Info',
            'message' => $config['message'] ?? '',
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function serializeMenu(Menu $menu): array
    {
        return [
            'label' => $menu->label,
            'route_name' => $menu->route_name,
            'url' => $menu->resolvedUrl(),
            'icon' => $menu->icon,
            'sort_order' => $menu->sort_order,
        ];
    }

    private function webSettingsTableExists(): bool
    {
        if (! $this->webSettingsTableChecked) {
            $this->webSettingsTableExists = Cache::rememberForever('web_settings_table_exists', function () {
                try {
                    return Schema::hasTable('web_settings');
                } catch (\Throwable) {
                    return false;
                }
            });
            $this->webSettingsTableChecked = true;
        }

        return $this->webSettingsTableExists;
    }

    private function cachedMenuForUser(callable $callback): mixed
    {
        $user = auth()->user();
        $key = "menu.{$user->id}";

        if (Cache::get('menu_sidebars') === null) {
            Cache::forget($key);
        }

        return Cache::remember($key, 3600, $callback);
    }

    public function navigationDomain(): ?string
    {
        if (! auth()->check()) {
            return null;
        }

        return app(UserNavigationDomain::class)->resolve(auth()->user());
    }
}
