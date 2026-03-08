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
        Schema::create('plugin_versions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('plugin_id')->constrained('plugins')->cascadeOnDelete();
            $table->string('version');
            $table->text('changelog')->nullable();
            $table->string('file_path')->nullable(); // path to zip in storage
            $table->string('file_name')->nullable(); // original zip filename
            $table->unsignedBigInteger('file_size')->default(0);
            $table->string('checksum')->nullable(); // sha256
            $table->string('requires_php')->default('7.4');
            $table->string('requires_wp')->default('5.8');
            $table->string('tested_wp')->nullable();
            $table->timestamp('released_at')->nullable();
            $table->timestamps();

            $table->unique(['plugin_id', 'version']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('plugin_versions');
    }
};
