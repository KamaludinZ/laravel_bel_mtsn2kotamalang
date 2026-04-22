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
        Schema::create('bell_types', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->boolean('is_active')->default(false);
            $table->jsonb('active_days')->nullable();
            $table->boolean('is_automatic')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bell_types');
    }
};
