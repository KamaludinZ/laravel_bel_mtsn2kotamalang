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
        Schema::table('audio_libraries', function (Blueprint $table) {
            $table->integer('duration')->nullable()->after('file_path')->comment('Duration in seconds');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('audio_libraries', function (Blueprint $table) {
            $table->dropColumn('duration');
        });
    }
};
