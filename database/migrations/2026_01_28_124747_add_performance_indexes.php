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
        Schema::table('spds', function (Blueprint $table) {
            $table->index(['employee_id', 'status']); // Compound index for filtering
            // $table->index(['departure_date', 'return_date']); // Removed duplicate (exists in 095515)
            $table->index('created_by'); // For access control
        });

        // Schema::table('approvals', function (Blueprint $table) {
        //     $table->index(['spd_id', 'status', 'level']); // Optimizing approval workflow lookups
        //     $table->index('approver_id');
        //     $table->index('created_at'); // Audit trail
        // });

        // Users table indexing removed as 'nip' column does not exist on users table
        // (It exists on employees table instead)
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('spds', function (Blueprint $table) {
            $table->dropIndex(['employee_id', 'status']);
            $table->dropIndex(['departure_date', 'return_date']);
            $table->dropIndex(['created_by']);
        });

        Schema::table('approvals', function (Blueprint $table) {
            $table->dropIndex(['spd_id', 'status', 'level']);
            $table->dropIndex(['approver_id']);
            $table->dropIndex(['created_at']);
        });

        // Users table dropIndex removed
    }
};
