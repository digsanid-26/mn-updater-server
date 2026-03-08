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
        Schema::create('plugins', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique(); // e.g. "mn-effects"
            $table->string('file_slug')->unique(); // e.g. "mn-effects/mn-effects.php"
            $table->text('description')->nullable();
            $table->string('author')->default('Digsan-Id');
            $table->string('author_uri')->default('https://www.digsan.it.com/');
            $table->string('homepage')->nullable();
            $table->string('requires_php')->default('7.4');
            $table->string('requires_wp')->default('5.8');
            $table->string('tested_wp')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('plugins');
    }
};
