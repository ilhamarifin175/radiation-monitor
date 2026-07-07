<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('monitor_luar', function (Blueprint $table) {
            $table->tinyInteger('is_backfill')->default(0)->after('latency_ms');
        });

        Schema::table('monitor_dalam', function (Blueprint $table) {
            $table->tinyInteger('is_backfill')->default(0)->after('latency_ms');
        });
    }

    public function down(): void
    {
        Schema::table('monitor_luar',  function (Blueprint $table) { $table->dropColumn('is_backfill'); });
        Schema::table('monitor_dalam', function (Blueprint $table) { $table->dropColumn('is_backfill'); });
    }
};
