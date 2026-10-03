<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // encrypted:array stores an encrypted string, not JSON.
        DB::statement('ALTER TABLE storage_settings MODIFY credentials LONGTEXT NULL');
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE storage_settings MODIFY credentials JSON NULL');
    }
};
