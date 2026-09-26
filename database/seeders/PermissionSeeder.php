<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Config;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Load permission structure from config
        $permissions = Config::get('permissions', []);

        // Create permissions
        foreach ($permissions as $section => $groups) {
            foreach ($groups as $group => $modules) {
                foreach ($modules as $module => $actions) {
                    foreach ($actions as $action => $label) {
                        $permissionName = "$section.$group.$module.$action";
                        Permission::updateOrCreate([
                            'name' => $permissionName,
                            'guard_name' => 'api',
                        ], [
                            'title' => ucwords($module.' '.$label),
                            'group' => $group.'-'.$module,
                        ]);
                    }
                }
            }
        }

        $superRoles = Role::whereIn('name', ['special-super-admin', 'super-admin'])->get();
        if ($superRoles->isNotEmpty()) {
            $allPermissions = Permission::all();
            foreach ($superRoles as $role) {
                $role->syncPermissions($allPermissions);
            }
        }
    }
}
