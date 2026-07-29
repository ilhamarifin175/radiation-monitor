<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('integrity_stats', function (Blueprint $table) {
            $table->renameColumn('esp_reset_count', 'esp_reset_count_luar');
        });

        Schema::table('integrity_stats', function (Blueprint $table) {
            $table->unsignedInteger('esp_reset_count_dalam')->default(0)->after('esp_reset_count_luar');
        });
    }

    public function down(): void
    {
        Schema::table('integrity_stats', function (Blueprint $table) {
            $table->dropColumn('esp_reset_count_dalam');
        });

        Schema::table('integrity_stats', function (Blueprint $table) {
            $table->renameColumn('esp_reset_count_luar', 'esp_reset_count');
        });
    }
};
