<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Drop tenant_id from kegiatan
        if (Schema::hasTable('kegiatan') && Schema::hasColumn('kegiatan', 'tenant_id')) {
            Schema::table('kegiatan', function (Blueprint $table) {
                $table->dropForeign(['tenant_id']);
                $table->dropColumn('tenant_id');
            });
        }

        // Drop tenant_id from pengaduan
        if (Schema::hasTable('pengaduan') && Schema::hasColumn('pengaduan', 'tenant_id')) {
            Schema::table('pengaduan', function (Blueprint $table) {
                $table->dropForeign(['tenant_id']);
                $table->dropColumn('tenant_id');
            });
        }

        // Drop tenant_id from keg_kesehatans
        if (Schema::hasTable('keg_kesehatans') && Schema::hasColumn('keg_kesehatans', 'tenant_id')) {
            Schema::table('keg_kesehatans', function (Blueprint $table) {
                $table->dropForeign(['tenant_id']);
                $table->dropColumn('tenant_id');
            });
        }

        // Drop pivot and tenants tables
        Schema::dropIfExists('tenant_user');
        Schema::dropIfExists('tenants');
    }

    public function down(): void
    {
        // Recreate tenants table (minimal) if rollback
        if (! Schema::hasTable('tenants')) {
            Schema::create('tenants', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('slug')->unique();
                $table->string('no_rt');
                $table->string('rw');
                $table->text('description')->nullable();
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });
        }

        // Recreate tenant_user pivot
        if (! Schema::hasTable('tenant_user')) {
            Schema::create('tenant_user', function (Blueprint $table) {
                $table->id();
                $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
                $table->foreignId('user_id')->constrained()->onDelete('cascade');
                $table->timestamps();
                $table->unique(['tenant_id', 'user_id']);
            });
        }

        // Re-add tenant_id columns (nullable) without data restore
        if (Schema::hasTable('kegiatan') && ! Schema::hasColumn('kegiatan', 'tenant_id')) {
            Schema::table('kegiatan', function (Blueprint $table) {
                $table->foreignId('tenant_id')->nullable()->constrained('tenants')->onDelete('cascade');
            });
        }

        if (Schema::hasTable('pengaduan') && ! Schema::hasColumn('pengaduan', 'tenant_id')) {
            Schema::table('pengaduan', function (Blueprint $table) {
                $table->unsignedBigInteger('tenant_id')->nullable()->after('id');
                $table->foreign('tenant_id')->references('id')->on('tenants')->onDelete('cascade');
            });
        }

        if (Schema::hasTable('keg_kesehatans') && ! Schema::hasColumn('keg_kesehatans', 'tenant_id')) {
            Schema::table('keg_kesehatans', function (Blueprint $table) {
                $table->foreignId('tenant_id')->nullable()->constrained('tenants')->onDelete('cascade');
            });
        }
    }
};
