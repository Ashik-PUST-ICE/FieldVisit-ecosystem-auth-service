<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        collect([
            'Special Super Admin',
            'Client',
            'Super Admin',
            'Billing',
            'Admin',
            'Support Engineer',
            'Revenue Assurance',
            'Business Development',
            'HR',
            'Head of Accounts',
            'System',
            'Transmission',
            'Office Staf',
            'Accounts Executive',
            'Technician',
            'Driver',
            'Office Assistant',
            'Call Center & Support Executive',
            'Developer',
            'System Admin',
            'Payment',
            'Reseller',
            'Advisor',
            'Store Executive',
        ])->each(fn ($role) => Role::firstOrCreate(
            ['name' => Str::slug($role), 'guard_name' => 'api', 'title' => $role]
        ));
    }
}
