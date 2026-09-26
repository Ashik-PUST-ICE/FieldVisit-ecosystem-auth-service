<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected $connection = 'directory';

    public function up(): void
    {
        Schema::create('user_network_indices', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('operational_branch_id')->nullable()->index();
            $table->unsignedBigInteger('network_id')->unique();
            $table->unsignedBigInteger('user_id')->index();
            $table->string('unique_id', 64)->nullable()->index();
            $table->string('first_name', 100)->nullable();
            $table->string('last_name', 100)->nullable();
            $table->string('full_name', 201)->nullable()->index();
            $table->string('email', 191)->nullable()->index();
            $table->string('mobile', 32)->nullable()->index();
            $table->string('whatsapp', 32)->nullable()->index();
            $table->date('dob')->nullable()->index();
            $table->unsignedBigInteger('user_group_id')->nullable()->index();

            $table->string('cid', 64)->nullable()->index();
            $table->unsignedBigInteger('mikrotik_id')->nullable()->index();
            $table->unsignedBigInteger('package_id')->nullable()->index();
            $table->string('package_name', 191)->nullable();
            $table->json('mikrotik')->nullable();
            $table->json('package')->nullable();

            $table->boolean('is_enabled_vat')->default(false);
            $table->decimal('vat_percentage', 3, 2)->nullable();

            $table->decimal('monthly_discount', 10, 2)->nullable();
            $table->date('next_cycle')->nullable();
            $table->string('connection_type', 50)->nullable();
            $table->string('pppoe_username', 191)->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->tinyInteger('network_status')->nullable()->index();
            $table->unsignedBigInteger('address_id')->nullable()->index();
            $table->boolean('is_auto_suspend')->default(true);
            $table->boolean('is_no_need_call')->default(false);
            $table->boolean('is_new_reconnection')->default(false);
            $table->timestamp('activation_date')->nullable();
            $table->timestamp('last_deactivated_at')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'cid']);

            $table->fullText(
                ['full_name', 'cid', 'email', 'mobile', 'package_name'],
                'fti_user_network_indices'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_network_indices');
    }
};
