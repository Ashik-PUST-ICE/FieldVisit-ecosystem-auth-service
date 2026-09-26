<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $admins = [
            [
                'first_name' => 'Admin',
                'last_name' => 'User',
                'unique_id' => 'SS-ADMIN-001',
                'email' => 'admin@example.com',
                'password' => bcrypt('password'),
            ],
            // Add more admin users as needed
        ];

        foreach ($admins as $admin) {

            $user = User::firstOrCreate(
                ['email' => $admin['email']],
                [
                    'first_name' => $admin['first_name'],
                    'last_name' => $admin['last_name'],
                    'unique_id' => $admin['unique_id'],
                    'password' => $admin['password'],
                    'status' => 1,
                ]
            );
            $user->syncRoles(['super-admin']);
        }
    }
}
