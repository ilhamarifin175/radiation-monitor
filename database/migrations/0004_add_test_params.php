<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Tambah latency_sec ke monitor_luar dan monitor_dalam
        Schema::table('monitor_luar', function (Blueprint $table) {
            $table->float('latency_sec')->nullable()->after('kelembapan');
        });

        Schema::table('monitor_dalam', function (Blueprint $table) {
            $table->float('latency_sec')->nullable()->after('rssi');
        });

        // Tambah parameter pengujian ke integrity_stats
        Schema::table('integrity_stats', function (Blueprint $table) {
            $table->unsignedInteger('esp_reset_count')->default(0)->after('lora_pdr');
            $table->unsignedInteger('backfill_luar')->default(0)->after('esp_reset_count');
            $table->unsignedInteger('backfill_dalam')->default(0)->after('backfill_luar');
            $table->unsignedInteger('backfill_stats')->default(0)->after('backfill_dalam');
        });
    }

    public function down(): void
    {
        Schema::table('monitor_luar',   function (Blueprint $table) { $table->dropColumn('latency_sec'); });
        Schema::table('monitor_dalam',  function (Blueprint $table) { $table->dropColumn('latency_sec'); });
        Schema::table('integrity_stats', function (Blueprint $table) {
            $table->dropColumn(['esp_reset_count', 'backfill_luar', 'backfill_dalam', 'backfill_stats']);
        });
    }
};
