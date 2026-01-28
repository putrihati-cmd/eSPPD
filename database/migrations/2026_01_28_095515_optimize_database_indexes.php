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
        // 1. Optimize SPPDs table
        Schema::table('spds', function (Blueprint $table) {
            $table->index('status'); 
            $table->index('employee_id'); 
            $table->index(['created_at', 'organization_id']); 
            $table->index(['departure_date', 'return_date']); 
        });

        // 2. Optimize Approvals
        Schema::table('approvals', function (Blueprint $table) {
            $table->index(['approver_id', 'status']); 
            $table->index(['spd_id', 'level']); 
        });

        // 3. Optimize Trip Reports
        Schema::table('trip_reports', function (Blueprint $table) {
            $table->index('is_verified');
            $table->index('submitted_at');
        });
        
        // 4. Optimize Employees
        Schema::table('employees', function (Blueprint $table) {
           $table->index('user_id'); 
           $table->index('unit_id'); 
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('spds', function (Blueprint $table) {
            $table->dropIndex(['status']);
            $table->dropIndex(['employee_id']);
            $table->dropIndex(['created_at', 'organization_id']);
            $table->dropIndex(['departure_date', 'return_date']);
        });
        
        Schema::table('approvals', function (Blueprint $table) {
            $table->dropIndex(['approver_id', 'status']);
            $table->dropIndex(['spd_id', 'level']);
        });

        Schema::table('trip_reports', function (Blueprint $table) {
            $table->dropIndex(['is_verified']);
            $table->dropIndex(['submitted_at']);
        });

        Schema::table('employees', function (Blueprint $table) {
            $table->dropIndex(['user_id']);
            $table->dropIndex(['unit_id']);
        });
    }
};
