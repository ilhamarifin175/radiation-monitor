<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('monitor_dalam', function (Blueprint $table) {
            $table->increments('id');
            $table->dateTime('timestamp');
            $table->unsignedInteger('seq');
            $table->float('cps');
            $table->float('usvh');
            $table->float('suhu');
            $table->float('kelembapan');
            $table->enum('relay', ['ON', 'OFF']);
            $table->enum('jaringan', ['WIFI', 'LORA']);
            $table->smallInteger('rssi')->nullable();
            $table->timestamp('created_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('monitor_dalam');
    }
};
