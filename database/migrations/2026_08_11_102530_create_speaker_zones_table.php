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
        Schema::create('speaker_zones', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name'); // Halaman, Kelas Lt 1, Masjid
            $table->string('description')->nullable();
            $table->integer('modbus_channel'); // 1-8 (relay channel number)
            $table->string('relay_mode')->default('normally_open'); // normally_open, normally_closed
            $table->boolean('is_enabled')->default(true);
            $table->integer('default_duration_seconds')->default(180); // 3 minutes default
            $table->integer('volume_level')->default(100); // 0-100 (if supported by hardware)
            $table->integer('sort_order')->default(0);
            $table->jsonb('schedule_override')->nullable(); // {monday: true, tuesday: false, ...}
            $table->timestamps();

            // Unique constraint: one zone per channel
            $table->unique('modbus_channel');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('speaker_zones');
    }
};
