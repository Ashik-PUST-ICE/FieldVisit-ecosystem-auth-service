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
        $expected = [];
        foreach ($permissions as $section => $groups) {
            foreach ($groups as $group => $modules) {
                foreach ($modules as $module => $actions) {
                    foreach ($actions as $action => $label) {
                        $permissionName = "$section.$group.$module.$action";
                        $expected[] = $permissionName;
                        Permission::updateOrCreate([
                            'name' => $permissionName,
                            'guard_name' => 'api',
                        ], [
                            'title' => ucwords(str_replace('_', ' ', $module.' '.$label)),
                            'group' => $group.'-'.$module,
                        ]);
                    }
                }
            }
        }

        // Prune permissions that are no longer in the catalogue (e.g. leftovers
        // from another project) so the role editor only shows real actions.
        Permission::where('guard_name', 'api')
            ->whereNotIn('name', $expected)
            ->delete();

        $superRoles = Role::whereIn('name', ['special-super-admin', 'super-admin'])->get();
        if ($superRoles->isNotEmpty()) {
            $allPermissions = Permission::all();
            foreach ($superRoles as $role) {
                $role->syncPermissions($allPermissions);
            }
        }
    }
}
