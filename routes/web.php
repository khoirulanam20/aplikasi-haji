<?php

use App\Http\Controllers\Auth\GoogleAuthController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Superadmin\ActivityLogController;
use App\Http\Controllers\Superadmin\ConfigController;
use App\Http\Controllers\Superadmin\DashboardController as SuperadminDashboardController;
use App\Http\Controllers\Superadmin\HajjParticipantController;
use App\Http\Controllers\Superadmin\ImpersonateController;
use App\Http\Controllers\Superadmin\MasterDataController;
use App\Http\Controllers\Superadmin\ModuleController;
use App\Http\Controllers\Superadmin\ModuleGroupController;
use App\Http\Controllers\Superadmin\ModuleOrderController;
use App\Http\Controllers\Superadmin\RolePermissionController;
use App\Http\Controllers\Superadmin\UserController;
use App\Http\Controllers\Superadmin\WebSettingController;
use App\Http\Controllers\TwoFactorSettingsController;
use App\Http\Controllers\UserTourController;
use App\Support\Auth\DashboardRedirect;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/privacy', fn () => Inertia::render('Legal/Privacy'))->name('privacy');

Route::get('/', function () {
    return redirect()->route('login');
});

Route::get('/auth/google/redirect', [GoogleAuthController::class, 'redirect'])->name('auth.google.redirect');
Route::get('/auth/google/callback', [GoogleAuthController::class, 'callback'])->name('auth.google.callback');

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', function () {
        $route = app(DashboardRedirect::class)->routeNameFor(auth()->user());

        return $route
            ? redirect()->route($route)
            : redirect()->route('access.pending');
    })->name('dashboard');

    Route::get('/access/pending', fn () => Inertia::render('Auth/AccessPending'))->name('access.pending');

    Route::get('/profile', [ProfileController::class, 'edit'])
        ->middleware('permission.any:profile.read,profile.list')
        ->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])
        ->middleware('permission.check:profile.update')
        ->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])
        ->middleware('permission.check:profile.update')
        ->name('profile.destroy');
    Route::get('/profile/export', [ProfileController::class, 'export'])
        ->middleware('permission.any:profile.read,profile.list')
        ->name('profile.export');
    Route::get('/profile/two-factor', [TwoFactorSettingsController::class, 'show'])
        ->middleware('permission.any:profile.read,profile.list')
        ->name('profile.two-factor');
    Route::post('/profile/two-factor/enable', [TwoFactorSettingsController::class, 'enable'])
        ->middleware('permission.check:profile.update')
        ->name('profile.two-factor.enable');
    Route::post('/profile/two-factor/confirm', [TwoFactorSettingsController::class, 'confirm'])
        ->middleware('permission.check:profile.update')
        ->name('profile.two-factor.confirm');
    Route::delete('/profile/two-factor', [TwoFactorSettingsController::class, 'destroy'])
        ->middleware('permission.check:profile.update')
        ->name('profile.two-factor.destroy');
    Route::post('/profile/tours/complete', [UserTourController::class, 'complete'])->name('profile.tours.complete');
    Route::get('/notifications', [NotificationController::class, 'index'])->middleware('permission.check:notifications.list')->name('notifications.index');
    Route::post('/notifications/{notificationId}/read', [NotificationController::class, 'markAsRead'])->middleware('permission.check:notifications.read')->name('notifications.read');
    Route::post('/impersonate/stop', [ImpersonateController::class, 'stop'])->name('impersonate.stop');

    Route::get('/superadmin/{path?}', function (?string $path = null) {
        $target = admin_url($path ?? 'dashboard');

        return redirect($target);
    })->where('path', '.*')->name('legacy.superadmin');
});

$adminPrefix = config('starterkit.admin_prefix');
$adminRouteName = config('starterkit.admin_route_name');

Route::prefix($adminPrefix)->name($adminRouteName.'.')->middleware(['auth'])->group(function (): void {
    Route::get('/dashboard', [SuperadminDashboardController::class, 'index'])->middleware('permission.check:dashboard.list')->name('dashboard');

    Route::get('/users', [UserController::class, 'index'])->middleware('permission.check:users.list')->name('users.index');
    Route::post('/users', [UserController::class, 'store'])->middleware('permission.check:users.create')->name('users.store');
    Route::put('/users/{user}', [UserController::class, 'update'])->middleware('permission.check:users.update')->name('users.update');
    Route::delete('/users/{user}', [UserController::class, 'destroy'])->middleware('permission.check:users.delete')->name('users.destroy');

    Route::get('/role-permission', [RolePermissionController::class, 'index'])->middleware('permission.check:roles.list')->name('role-permission.index');
    Route::post('/role-permission/roles', [RolePermissionController::class, 'storeRole'])->middleware('permission.check:roles.create')->name('role-permission.roles.store');
    Route::put('/role-permission/roles/{role}', [RolePermissionController::class, 'updateRole'])->middleware('permission.check:roles.update')->name('role-permission.roles.update');
    Route::delete('/role-permission/roles/{role}', [RolePermissionController::class, 'destroyRole'])->middleware('permission.check:roles.delete')->name('role-permission.roles.destroy');
    Route::put('/role-permission/roles/{role}/permissions', [RolePermissionController::class, 'updatePermissions'])->middleware('permission.check:roles.assign')->name('role-permission.permissions.update');

    Route::get('/modules', [ModuleController::class, 'index'])->middleware('permission.check:modules.list')->name('modules.index');
    Route::put('/modules/reorder', [ModuleOrderController::class, 'reorderModules'])->middleware('permission.check:modules.update')->name('modules.reorder');
    Route::post('/modules', [ModuleController::class, 'store'])->middleware('permission.check:modules.create')->name('modules.store');
    Route::get('/modules/{module}', [ModuleController::class, 'show'])->middleware('permission.check:modules.show')->name('modules.show');
    Route::put('/modules/{module}', [ModuleController::class, 'update'])->middleware('permission.check:modules.update')->name('modules.update');
    Route::delete('/modules/{module}', [ModuleController::class, 'destroy'])->middleware('permission.check:modules.delete')->name('modules.destroy');

    Route::post('/module-groups', [ModuleGroupController::class, 'store'])->middleware('permission.check:module-groups.create')->name('module-groups.store');
    Route::put('/module-groups/reorder', [ModuleOrderController::class, 'reorderGroups'])->middleware('permission.check:module-groups.update')->name('module-groups.reorder');
    Route::put('/module-groups/{moduleGroup}', [ModuleGroupController::class, 'update'])->middleware('permission.check:module-groups.update')->name('module-groups.update');
    Route::delete('/module-groups/{moduleGroup}', [ModuleGroupController::class, 'destroy'])->middleware('permission.check:module-groups.delete')->name('module-groups.destroy');

    Route::post('/impersonate/{user}', [ImpersonateController::class, 'start'])->middleware('permission.check:impersonate.start')->name('impersonate.start');
    Route::get('/master-data', [MasterDataController::class, 'index'])->middleware('permission.check:masterdata.list')->name('master-data.index');
    Route::post('/master-data', [MasterDataController::class, 'store'])->middleware('permission.check:masterdata.create')->name('master-data.store');
    Route::put('/master-data/{master_datum}', [MasterDataController::class, 'update'])->middleware('permission.check:masterdata.update')->name('master-data.update');
    Route::delete('/master-data/{master_datum}', [MasterDataController::class, 'destroy'])->middleware('permission.check:masterdata.delete')->name('master-data.destroy');

    Route::get('/hajj-participants', [HajjParticipantController::class, 'index'])->middleware('permission.check:hajj_participants.list')->name('hajj-participants.index');
    Route::post('/hajj-participants', [HajjParticipantController::class, 'store'])->middleware('permission.check:hajj_participants.create')->name('hajj-participants.store');
    Route::put('/hajj-participants/{hajj_participant}', [HajjParticipantController::class, 'update'])->middleware('permission.check:hajj_participants.update')->name('hajj-participants.update');
    Route::delete('/hajj-participants/{hajj_participant}', [HajjParticipantController::class, 'destroy'])->middleware('permission.check:hajj_participants.delete')->name('hajj-participants.destroy');
    Route::get('/hajj-participants/import/template', [HajjParticipantController::class, 'downloadImportTemplate'])->middleware('permission.check:hajj_participants.import')->name('hajj-participants.import.template');
    Route::post('/hajj-participants/import/preview', [HajjParticipantController::class, 'previewImport'])->middleware('permission.check:hajj_participants.import')->name('hajj-participants.import.preview');
    Route::get('/hajj-participants/import/status/{token}', [HajjParticipantController::class, 'importStatus'])->middleware('permission.check:hajj_participants.import')->name('hajj-participants.import.status');
    Route::post('/hajj-participants/import', [HajjParticipantController::class, 'import'])->middleware('permission.check:hajj_participants.import')->name('hajj-participants.import');
    Route::get('/web-setting', [WebSettingController::class, 'index'])->middleware('permission.any:websetting.list,websetting.read')->name('web-setting.index');
    Route::put('/web-setting', [WebSettingController::class, 'update'])->middleware('permission.all:websetting.create,websetting.update')->name('web-setting.update');
    Route::get('/config', [ConfigController::class, 'index'])->middleware('permission.any:config.list,config.read')->name('config.index');
    Route::put('/config', [ConfigController::class, 'update'])->middleware('permission.all:config.create,config.update')->name('config.update');
    Route::get('/activity-log', [ActivityLogController::class, 'index'])->middleware('permission.check:activitylog.list')->name('activity-log.index');
    Route::delete('/activity-log/{activity}', [ActivityLogController::class, 'destroy'])->middleware('permission.check:activitylog.delete')->name('activity-log.destroy');
});

require __DIR__.'/auth.php';
