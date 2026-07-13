<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Settings\RoleController;
use App\Http\Controllers\Settings\ModuleController;
use App\Http\Controllers\Settings\SettingsController;
use App\Http\Controllers\Settings\ActivityLogController;

Route::middleware([
    'auth'
])->group(function () {
    Route::get('roles/permissions/{id}', [RoleController::class, 'getPermission'])->name('roles.permissions.get');
    Route::put('roles/{role}/permissions/update',  [RoleController::class, 'updatePermission'])->name('roles.permissions.update');
    Route::get('roles/role-name', [RoleController::class, 'getRoleName']);
    Route::resource('roles', RoleController::class);
    
    Route::resource('modules', ModuleController::class);
    // All settings page
    Route::get('settings/all', [SettingsController::class, 'all'])->name('settings.all');
    // Additional route for settings by group (pages)
    Route::get('settings/group/{group}', [SettingsController::class, 'group'])->name('settings.group');
    Route::get('settings/group/{group}/create', [SettingsController::class, 'create'])->name('settings.group.create');
    Route::get('settings/group/{group}/edit', [SettingsController::class, 'groupEdit'])->name('settings.group.edit');
    Route::put('settings/group/{group}/update', [SettingsController::class, 'updateMultiple'])->name('settings.group.update');
    Route::resource('settings', SettingsController::class);

    // Activity Logs
    Route::get('activity-logs', [ActivityLogController::class, 'index'])->name('activity-logs.index');
});