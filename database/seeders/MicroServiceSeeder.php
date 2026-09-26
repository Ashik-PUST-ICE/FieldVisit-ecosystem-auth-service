<?php

namespace Database\Seeders;

use App\Models\MicroService;
use Illuminate\Database\Seeder;

class MicroServiceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $services = [
            [
                'name' => 'auth-service',
                'base_uri' => 'http://auth-service.local',
            ],
            [
                'name' => 'saltsync-service',
                'base_uri' => 'http://saltsync-service.local',
            ],
            [
                'name' => 'notification-service',
                'base_uri' => 'http://notification-service.local',
            ],
            [
                'name' => 'whatsapp-service',
                'base_uri' => 'http://whatsapp-service.local',
            ],
            [
                'name' => 'logging-service',
                'base_uri' => 'http://logging-service.local',
            ],
        ];

        foreach ($services as $service) {
            MicroService::updateOrCreate(
                ['name' => $service['name']],
                ['base_uri' => $service['base_uri']]
            );
        }
    }
}
