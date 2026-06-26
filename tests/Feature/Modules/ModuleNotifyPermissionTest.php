<?php

namespace Tests\Feature\Modules;

use App\Models\Module;
use App\Models\ModuleGroup;
use App\Models\User;
use App\Support\Modules\ModuleRegistryService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use App\Notifications\CrudActivityNotification;
use App\Support\Modules\ModuleNotification;
use Spatie\Permission\Models\Permission;
use App\Models\Role;
use Tests\TestCase;

class ModuleNotifyPermissionTest extends TestCase
{
    use RefreshDatabase;

    public function test_module_with_notifications_creates_notify_permission(): void
    {
        Permission::create(['name' => 'modules.create', 'guard_name' => 'web']);
        $role = Role::create(['name' => 'superadmin', 'guard_name' => 'web', 'title' => 'Superadmin', 'is_active' => true]);
        $role->givePermissionTo('modules.create');

        $user = User::factory()->create();
        $user->assignRole('superadmin');

        $group = ModuleGroup::create(['name' => 'Data', 'code' => 'data', 'is_active' => true]);

        $response = $this->actingAs($user)->post('/app/modules', [
            'module_group_id' => $group->id,
            'title' => 'Laporan',
            'code' => 'laporan',
            'route_name' => 'app.dashboard',
            'layout_type' => Module::LAYOUT_TABLE_BASE,
            'is_active' => true,
            'show_in_sidebar' => false,
            'enabled_actions' => ['list', 'notify'],
            'custom_actions' => [],
        ]);

        $response->assertRedirect();
        $response->assertSessionHasNoErrors();

        $this->assertDatabaseHas('modules', [
            'code' => 'laporan',
            'has_notifications' => true,
        ]);

        $this->assertDatabaseHas('module_actions', [
            'action' => 'notify',
            'is_enabled' => true,
        ]);

        $this->assertDatabaseHas('permissions', ['name' => 'laporan.notify']);
    }

    public function test_notify_not_enabled_when_unchecked_in_rbac(): void
    {
        Permission::create(['name' => 'modules.create', 'guard_name' => 'web']);
        $role = Role::create(['name' => 'superadmin', 'guard_name' => 'web', 'title' => 'Superadmin', 'is_active' => true]);
        $role->givePermissionTo('modules.create');

        $user = User::factory()->create();
        $user->assignRole('superadmin');

        $group = ModuleGroup::create(['name' => 'Data', 'code' => 'data', 'is_active' => true]);

        $this->actingAs($user)->post('/app/modules', [
            'module_group_id' => $group->id,
            'title' => 'Tanpa Notif',
            'code' => 'tanpa_notif',
            'route_name' => 'app.dashboard',
            'layout_type' => Module::LAYOUT_TABLE_BASE,
            'is_active' => true,
            'show_in_sidebar' => false,
            'enabled_actions' => ['list'],
            'custom_actions' => [],
        ])->assertRedirect();

        $module = Module::where('code', 'tanpa_notif')->first();
        $this->assertNotNull($module);
        $this->assertFalse($module->has_notifications);
        $this->assertFalse($module->actions()->where('action', 'notify')->where('is_enabled', true)->exists());
        $this->assertDatabaseMissing('permissions', ['name' => 'tanpa_notif.notify']);
    }

    public function test_module_notification_helper_respects_flag_and_permission(): void
    {
        Notification::fake();

        $group = ModuleGroup::create(['name' => 'Data', 'code' => 'data', 'is_active' => true]);
        $module = Module::create([
            'module_group_id' => $group->id,
            'title' => 'Master Data',
            'code' => 'masterdata',
            'route_name' => 'app.master-data.index',
            'has_notifications' => true,
            'is_active' => true,
            'show_in_sidebar' => true,
        ]);

        app(ModuleRegistryService::class)->syncActions($module, ['list', 'notify']);

        $user = User::factory()->create();
        $user->givePermissionTo('masterdata.notify');

        ModuleNotification::sendIfAllowed($user, 'masterdata', 'Judul', 'Pesan');

        Notification::assertSentTo($user, CrudActivityNotification::class);

        $userWithout = User::factory()->create();
        Notification::fake();

        ModuleNotification::sendIfAllowed($userWithout, 'masterdata', 'Judul', 'Pesan');

        Notification::assertNothingSent();
    }
}
