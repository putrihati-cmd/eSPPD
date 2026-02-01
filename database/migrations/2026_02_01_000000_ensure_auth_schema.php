<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // Ensure is_password_reset field exists on users table
        if (!Schema::hasColumn('users', 'is_password_reset')) {
            Schema::table('users', function (Blueprint $table) {
                $table->boolean('is_password_reset')->default(false)->after('password');
            });
        }

        // Ensure user_id foreign key exists on employees table
        if (!Schema::hasColumn('employees', 'user_id')) {
            Schema::table('employees', function (Blueprint $table) {
                $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null');
            });
        }
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('is_password_reset');
        });

        Schema::table('employees', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropColumn('user_id');
        });
    }
};
