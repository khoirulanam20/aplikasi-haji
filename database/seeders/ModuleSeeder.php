<?php

namespace Database\Seeders;

use App\Models\Module;
use App\Models\ModuleGroup;
use App\Models\Role;
use App\Support\Modules\ModuleRegistryService;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

class ModuleSeeder extends Seeder
{
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $registry = app(ModuleRegistryService::class);

        $definitions = [
            [
                'group' => [
                    'name' => 'Umum',
                    'code' => 'umum',
                    'sort_order' => 10,
                ],
                'modules' => [
                    [
                        'title' => 'Dashboard',
                        'code' => 'dashboard',
                        'route_name' => 'app.dashboard',
                        'icon' => '<i class="iconoir-home-simple"></i>',
                        'sort_order' => 10,
                        'actions' => ['list'],
                    ],
                ],
            ],
            [
                'group' => [
                    'name' => 'Pengaturan Pengguna',
                    'code' => 'pengaturan_pengguna',
                    'sort_order' => 20,
                ],
                'modules' => [
                    [
                        'title' => 'Pengguna',
                        'code' => 'users',
                        'route_name' => 'app.users.index',
                        'icon' => '<i class="iconoir-group"></i>',
                        'sort_order' => 20,
                        'actions' => ['list', 'create', 'update', 'delete'],
                    ],
                    [
                        'title' => 'Role Permission',
                        'code' => 'roles',
                        'route_name' => 'app.role-permission.index',
                        'icon' => '<i class="iconoir-shield"></i>',
                        'sort_order' => 30,
                        'actions' => ['list', 'create', 'update', 'delete', 'assign'],
                    ],
                    [
                        'title' => 'Modules',
                        'code' => 'modules',
                        'route_name' => 'app.modules.index',
                        'icon' => '<i class="iconoir-view-grid"></i>',
                        'sort_order' => 40,
                        'actions' => ['list', 'show', 'create', 'update', 'delete'],
                    ],
                ],
            ],
            [
                'group' => [
                    'name' => 'Data',
                    'code' => 'data',
                    'sort_order' => 30,
                ],
                'modules' => [
                    [
                        'title' => 'Peserta Haji',
                        'code' => 'hajj_participants',
                        'route_name' => 'app.hajj-participants.index',
                        'icon' => '<i class="iconoir-user-circle"></i>',
                        'sort_order' => 45,
                        'actions' => ['list', 'create', 'edit', 'update', 'delete', 'import'],
                        'custom_actions' => [
                            ['action' => 'import', 'label' => 'Import'],
                        ],
                    ],
                    [
                        'title' => 'Master Data',
                        'code' => 'masterdata',
                        'route_name' => 'app.master-data.index',
                        'icon' => '<i class="iconoir-database"></i>',
                        'sort_order' => 50,
                        'has_notifications' => true,
                        'actions' => ['list', 'create', 'edit', 'update', 'delete', 'notify'],
                    ],
                ],
            ],
            [
                'group' => [
                    'name' => 'Pengaturan Website',
                    'code' => 'pengaturan_website',
                    'sort_order' => 40,
                ],
                'modules' => [
                    [
                        'title' => 'Web Setting',
                        'code' => 'websetting',
                        'layout_type' => Module::LAYOUT_FORM_BASE,
                        'route_name' => 'app.web-setting.index',
                        'icon' => '<i class="iconoir-settings"></i>',
                        'sort_order' => 60,
                        'actions' => ['list', 'read', 'create', 'update'],
                    ],
                    [
                        'title' => 'Config',
                        'code' => 'config',
                        'layout_type' => Module::LAYOUT_FORM_BASE,
                        'route_name' => 'app.config.index',
                        'icon' => '<i class="iconoir-code"></i>',
                        'sort_order' => 65,
                        'actions' => ['list', 'read', 'create', 'update'],
                    ],
                ],
            ],
            [
                'group' => [
                    'name' => 'Audit',
                    'code' => 'audit',
                    'sort_order' => 50,
                ],
                'modules' => [
                    [
                        'title' => 'Log Aktivitas',
                        'code' => 'activitylog',
                        'route_name' => 'app.activity-log.index',
                        'icon' => '<i class="iconoir-history"></i>',
                        'sort_order' => 10,
                        'actions' => ['list', 'delete'],
                    ],
                ],
            ],
        ];

        foreach ($definitions as $definition) {
            $group = ModuleGroup::updateOrCreate(
                ['code' => $definition['group']['code']],
                [
                    'name' => $definition['group']['name'],
                    'sort_order' => $definition['group']['sort_order'],
                    'is_active' => true,
                ]
            );

            foreach ($definition['modules'] as $moduleData) {
                $module = Module::updateOrCreate(
                    ['code' => $moduleData['code']],
                    [
                        'module_group_id' => $group->id,
                        'title' => $moduleData['title'],
                        'layout_type' => $moduleData['layout_type'] ?? Module::LAYOUT_TABLE_BASE,
                        'route_name' => $moduleData['route_name'],
                        'icon' => $moduleData['icon'],
                        'sort_order' => $moduleData['sort_order'],
                        'is_active' => true,
                        'show_in_sidebar' => true,
                        'has_notifications' => $moduleData['has_notifications'] ?? false,
                    ]
                );

                $registry->syncActions(
                    $module,
                    $moduleData['actions'],
                    $moduleData['custom_actions'] ?? [],
                );
            }
        }

        $superadmin = Role::where('name', 'superadmin')->where('guard_name', 'web')->first();
        if ($superadmin) {
            $superadmin->syncPermissions(
                Permission::query()->where('guard_name', 'web')->pluck('name')
            );
        }

    }
}
