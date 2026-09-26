<?php

namespace Database\Seeders;

use App\Jobs\UserImportJob;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        dispatch(new UserImportJob);
    }
}
