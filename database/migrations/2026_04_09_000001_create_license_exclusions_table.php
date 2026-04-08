<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('license_exclusions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('license_key_id')->constrained()->cascadeOnDelete();
            $table->enum('item_type', ['plugin', 'theme']);
            $table->unsignedBigInteger('item_id');
            $table->boolean('is_excluded')->default(false);
            $table->timestamps();

            $table->unique(['license_key_id', 'item_type', 'item_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('license_exclusions');
    }
};
