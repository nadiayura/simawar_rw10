<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('ketua_rts', function (Blueprint $table) {
            // Drop the old unique constraint first
            $table->dropUnique(['no_rt', 'is_active']);
            
            // Add jabatan field
            $table->enum('jabatan', ['Ketua RT', 'Sekretaris RT', 'Bendahara RT'])
                  ->default('Ketua RT')
                  ->after('no_rt');
            
            // Add new unique constraint that includes jabatan
            $table->unique(['no_rt', 'jabatan', 'is_active'], 'unique_rt_jabatan_active');
            
            // Add index for jabatan
            $table->index('jabatan');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ketua_rts', function (Blueprint $table) {
            // Drop the new unique constraint
            $table->dropUnique('unique_rt_jabatan_active');
            
            // Drop jabatan index
            $table->dropIndex(['jabatan']);
            
            // Drop jabatan field
            $table->dropColumn('jabatan');
            
            // Restore the old unique constraint
            $table->unique(['no_rt', 'is_active']);
        });
    }
};
