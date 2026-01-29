<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Remove the restrictive check constraint on the role column.
        // This allows us to store granular roles like 'superadmin', 'rektor', 'dekan'.
        DB::statement("ALTER TABLE users DROP CONSTRAINT IF EXISTS users_role_check");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Restore the constraint if needed (though it conflicts with the new architecture)
        // Original constraint values were: admin, approver, employee
        DB::statement("ALTER TABLE users ADD CONSTRAINT users_role_check CHECK (role::text = ANY (ARRAY['admin'::character varying, 'approver'::character varying, 'employee'::character varying]::text[]))");
    }
};
