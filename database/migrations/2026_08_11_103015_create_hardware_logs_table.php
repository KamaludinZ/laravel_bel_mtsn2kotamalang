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
        Schema::create('hardware_logs', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('command_queue_id')->nullable();
            $table->uuid('speaker_zone_id')->nullable();
            $table->string('action'); // trigger_bell, test_speaker, relay_on, relay_off, status_check
            $table->string('status'); // success, failed, timeout
            $table->text('message')->nullable();
            $table->jsonb('request_data')->nullable();
            $table->jsonb('response_data')->nullable();
            $table->integer('execution_time_ms')->nullable(); // How long it took
            $table->string('bridge_version')->nullable(); // Bridge service version
            $table->string('bridge_ip')->nullable(); // IP address of bridge
            $table->timestamps();

            // Indexes
            $table->index('action');
            $table->index('status');
            $table->index('created_at');
            $table->foreign('command_queue_id')->references('id')->on('hardware_command_queue')->onDelete('set null');
            $table->foreign('speaker_zone_id')->references('id')->on('speaker_zones')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hardware_logs');
    }
};
