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
        Schema::create('bell_schedules', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('bell_type_id')->constrained('bell_types')->onDelete('cascade');
            $table->enum('day', ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday']);
            $table->time('time');
            $table->foreignUuid('audio_library_id')->constrained('audio_libraries')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bell_schedules');
    }
};
