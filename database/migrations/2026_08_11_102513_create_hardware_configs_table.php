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
        Schema::create('hardware_configs', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('config_key')->unique(); // 'primary_device', 'backup_device'
            $table->string('device_type')->default('modbus_rs485'); // modbus_rs485, serial_relay
            $table->string('connection_type')->default('usb'); // usb, network
            $table->string('com_port')->nullable(); // COM3, /dev/ttyUSB0
            $table->integer('baud_rate')->default(9600);
            $table->integer('data_bits')->default(8);
            $table->string('parity')->default('N'); // N, E, O
            $table->integer('stop_bits')->default(1);
            $table->integer('modbus_address')->default(1);
            $table->boolean('is_enabled')->default(true);
            $table->boolean('auto_reconnect')->default(true);
            $table->integer('timeout_ms')->default(1000);
            $table->jsonb('extra_config')->nullable(); // Additional settings
            $table->timestamp('last_connected_at')->nullable();
            $table->string('last_status')->nullable(); // online, offline, error
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hardware_configs');
    }
};
