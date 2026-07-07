<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('monitor_luar',  function (Blueprint $table) { $table->dropColumn('timestamp'); });
        Schema::table('monitor_dalam', function (Blueprint $table) { $table->dropColumn('timestamp'); });
    }

    public function down(): void
    {
        Schema::table('monitor_luar',  function (Blueprint $table) { $table->dateTime('timestamp')->after('id'); });
        Schema::table('monitor_dalam', function (Blueprint $table) { $table->dateTime('timestamp')->after('id'); });
    }
};
