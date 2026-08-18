<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('fonnte_devices')) {
            Schema::create('fonnte_devices', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('device');
                $table->string('token');
                $table->string('status')->default('inactive');
                $table->timestamp('last_synced_at')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('fonnte_devices');
    }
};
