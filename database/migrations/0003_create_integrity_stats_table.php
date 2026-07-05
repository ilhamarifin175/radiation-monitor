<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('integrity_stats', function (Blueprint $table) {
            $table->increments('id');
            $table->dateTime('timestamp');
            $table->unsignedInteger('wifi_terima');
            $table->unsignedInteger('wifi_hilang');
            $table->float('wifi_pdr');
            $table->unsignedInteger('lora_terima');
            $table->unsignedInteger('lora_hilang');
            $table->float('lora_pdr');
            $table->timestamp('created_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('integrity_stats');
    }
};
