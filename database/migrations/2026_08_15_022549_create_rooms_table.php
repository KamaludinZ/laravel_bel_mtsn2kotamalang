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
        Schema::create('rooms', function (Blueprint $table) {
            $table->id();
            $table->integer('no')->unique(); // Nomor urut room
            $table->string('room_code', 10)->unique(); // Kode ruang (7A, 7B, 8C, dll)
            $table->string('room_type'); // Jenis ruang (CLASS, HORN, CTRLROOM, LIBRARY, MAHAD, dll)
            $table->string('room_name'); // Nama ruang (HORN, CTRLROOM, CLASS, 7C, 8K, LIBRARY, MAHAD 1, dll)
            $table->string('group_name'); // Nama grup (CUSTOM 1, CUSTOM 2, GROUP 1-6)
            $table->string('hardware_address')->nullable(); // Alamat hardware (1-1, 10-4, 9-1, dll)
            $table->foreignUuid('speaker_zone_id')->nullable()->constrained('speaker_zones')->nullOnDelete(); // FK ke speaker_zones
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();

            // Indexes
            $table->index('group_name');
            $table->index('room_type');
            $table->index('is_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rooms');
    }
};
