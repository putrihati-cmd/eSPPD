<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Add missing fields from ceking.md specification to spds table
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('spds', function (Blueprint $table) {
            // Travel type (from ceking.md)
            $table->enum('travel_type', ['dalam_kota', 'luar_kota', 'luar_negeri'])
                  ->default('dalam_kota')
                  ->after('transport_type');
            
            // Current approver tracking (from ceking.md)
            $table->string('current_approver_nip')->nullable()->after('status');
            
            // Rejection reason (from ceking.md)
            $table->text('rejection_reason')->nullable()->after('current_approver_nip');
            
            // Final approval info (from ceking.md)
            $table->timestamp('approved_at')->nullable()->after('rejection_reason');
            $table->uuid('approved_by')->nullable()->after('approved_at');
            
            // Add foreign key for approved_by
            $table->foreign('approved_by')
                  ->references('id')
                  ->on('employees')
                  ->onDelete('set null');
        });
        
        // Also add approver_nip to approvals table for audit trail consistency
        Schema::table('approvals', function (Blueprint $table) {
            $table->string('approver_nip')->nullable()->after('approver_id');
            $table->integer('approver_level')->default(1)->after('approver_nip');
            $table->enum('action', ['approve', 'reject', 'forward'])->nullable()->after('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('spds', function (Blueprint $table) {
            $table->dropForeign(['approved_by']);
            $table->dropColumn([
                'travel_type',
                'current_approver_nip',
                'rejection_reason',
                'approved_at',
                'approved_by'
            ]);
        });
        
        Schema::table('approvals', function (Blueprint $table) {
            $table->dropColumn(['approver_nip', 'approver_level', 'action']);
        });
    }
};
