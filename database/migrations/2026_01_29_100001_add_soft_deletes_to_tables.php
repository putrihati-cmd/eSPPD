<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Add soft deletes to spds and employees tables
 * 
 * Reason: Data SPPD adalah data keuangan negara. BPK harus bisa audit.
 * Tidak boleh dihapus permanen. Tombol "Hapus" hanya soft delete.
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Add soft delete columns to spds table
        Schema::table('spds', function (Blueprint $table) {
            $table->softDeletes(); // Kolom deleted_at
            $table->string('deleted_by')->nullable()->after('completed_at'); // Siapa yang hapus (NIP)
            $table->text('deleted_reason')->nullable()->after('deleted_by'); // Alasan dihapus
        });
        
        // Add soft delete columns to employees table
        Schema::table('employees', function (Blueprint $table) {
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('spds', function (Blueprint $table) {
            $table->dropSoftDeletes();
            $table->dropColumn(['deleted_by', 'deleted_reason']);
        });
        
        Schema::table('employees', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });
    }
};
