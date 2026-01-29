<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Add revision tracking fields to spds table
 * 
 * Purpose: Ketika SPPD ditolak, pegawai bisa edit dan ajukan ulang
 * tanpa harus membuat SPPD baru dari nol.
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('spds', function (Blueprint $table) {
            // Revision tracking
            $table->integer('revision_count')->default(0)->after('approved_by');
            $table->json('revision_history')->nullable()->after('revision_count');
            
            // Rejection tracking
            $table->timestamp('rejected_at')->nullable()->after('revision_history');
            $table->string('rejected_by')->nullable()->after('rejected_at'); // NIP yang menolak
            
            // Previous approver for resubmission
            $table->string('previous_approver_nip')->nullable()->after('rejected_by');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('spds', function (Blueprint $table) {
            $table->dropColumn([
                'revision_count',
                'revision_history',
                'rejected_at',
                'rejected_by',
                'previous_approver_nip',
            ]);
        });
    }
};
