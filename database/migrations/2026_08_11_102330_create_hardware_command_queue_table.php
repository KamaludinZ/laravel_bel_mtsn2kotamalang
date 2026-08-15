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
        Schema::create('hardware_command_queue', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('command_type'); // trigger_bell, test_speaker, relay_on, relay_off
            $table->jsonb('payload')->nullable(); // {zone_id: 1, duration: 180, audio_id: xxx}
            $table->string('status')->default('pending'); // pending, processing, completed, failed, expired
            $table->timestamp('scheduled_at')->nullable(); // When to execute
            $table->timestamp('executed_at')->nullable(); // When actually executed
            $table->timestamp('expires_at')->nullable(); // Auto-expire old commands
            $table->text('error_message')->nullable();
            $table->jsonb('response_data')->nullable(); // Response from bridge
            $table->integer('retry_count')->default(0);
            $table->timestamps();

            // Indexes for performance
            $table->index('status');
            $table->index('scheduled_at');
            $table->index('expires_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hardware_command_queue');
    }
};
