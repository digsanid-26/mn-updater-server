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
        Schema::create('update_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('domain_id')->nullable()->constrained('domains')->nullOnDelete();
            $table->foreignId('plugin_id')->nullable()->constrained('plugins')->nullOnDelete();
            $table->string('domain_name')->nullable(); // denormalized for log persistence
            $table->string('plugin_slug')->nullable(); // denormalized
            $table->string('from_version')->nullable();
            $table->string('to_version')->nullable();
            $table->string('action'); // check_update, download, validate_license, deactivate_license
            $table->string('status')->default('success'); // success, failed, denied
            $table->string('ip_address')->nullable();
            $table->text('metadata')->nullable(); // JSON extra data
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('update_logs');
    }
};
