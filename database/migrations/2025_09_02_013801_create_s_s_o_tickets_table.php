<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('sso_tickets', function (Blueprint $table) {
            $table->uuid('id')->primary();     // tid
            $table->text('payload');           // encrypted tokens JSON
            $table->timestamp('expires_at');   // short TTL (e.g., 60s)
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sso_tickets');
    }
};
