<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('monitor_luar', function (Blueprint $table) {
            $table->unsignedInteger('latency_ms')->nullable()->after('latency_sec');
            $table->dropColumn('latency_sec');
        });

        Schema::table('monitor_dalam', function (Blueprint $table) {
            $table->unsignedInteger('latency_ms')->nullable()->after('latency_sec');
            $table->dropColumn('latency_sec');
        });
    }

    public function down(): void
    {
        Schema::table('monitor_luar', function (Blueprint $table) {
            $table->float('latency_sec')->nullable()->after('kelembapan');
            $table->dropColumn('latency_ms');
        });

        Schema::table('monitor_dalam', function (Blueprint $table) {
            $table->float('latency_sec')->nullable()->after('rssi');
            $table->dropColumn('latency_ms');
        });
    }
};
