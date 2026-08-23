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
        Schema::table('rooms', function (Blueprint $table) {
            // Add parent_hardware_address column
            $table->string('parent_hardware_address')->nullable()->after('hardware_address');

            // Add index for faster queries
            $table->index('parent_hardware_address');
        });

        // Automatically populate parent_hardware_address
        // All rooms except HORN (10-4) and CTRL ROOM (11-4) use CTRL ROOM as parent
        DB::table('rooms')
            ->whereNotIn('hardware_address', ['10-4', '11-4'])
            ->whereNotNull('hardware_address')
            ->update([
                'parent_hardware_address' => '11-4'
            ]);

        // HORN and CTRL ROOM have no parent (they ARE parents)
        DB::table('rooms')
            ->whereIn('hardware_address', ['10-4', '11-4'])
            ->update([
                'parent_hardware_address' => null
            ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('rooms', function (Blueprint $table) {
            $table->dropIndex(['parent_hardware_address']);
            $table->dropColumn('parent_hardware_address');
        });
    }
};
