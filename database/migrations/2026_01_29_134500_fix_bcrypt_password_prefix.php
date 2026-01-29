<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Fix bcrypt algorithm prefix mismatch.
     * 
     * Python bcrypt uses $2b$ prefix but Laravel expects $2y$ or $2a$
     * Both are compatible, just need to change the prefix.
     */
    public function up(): void
    {
        // Update all passwords from $2b$ to $2y$ (they are cryptographically identical)
        DB::statement("UPDATE users SET password = '$2y' || SUBSTRING(password FROM 4) WHERE password LIKE '$2b$%'");
    }

    public function down(): void
    {
        // Revert back to $2b$ if needed
        DB::statement("UPDATE users SET password = '$2b' || SUBSTRING(password FROM 4) WHERE password LIKE '$2y$%'");
    }
};
