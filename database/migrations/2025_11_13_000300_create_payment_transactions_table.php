<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('payment_transactions')) {
            Schema::create('payment_transactions', function (Blueprint $table) {
                $table->id();
                $table->foreignId('tagihan_iuran_id')->constrained('tagihan_iuran_wargas');
                $table->decimal('amount', 15, 2);
                $table->string('status')->nullable();
                $table->string('payment_type')->nullable();
                $table->string('snap_token')->nullable();
                $table->string('redirect_url')->nullable();
                $table->string('transaction_id')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_transactions');
    }
};
