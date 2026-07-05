<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('monitor_luar', function (Blueprint $table) {
            $table->increments('id');
            $table->dateTime('timestamp');
            $table->float('cps');
            $table->float('usvh');
            $table->float('suhu');
            $table->float('kelembapan');
            $table->timestamp('created_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('monitor_luar');
    }
};
