<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * RBAC.md Implementation: roles table
 * Stores role definitions with hierarchy levels
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('roles', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();        // superadmin, admin, rektor, etc
            $table->string('label');                  // Display name
            $table->integer('level')->default(1);     // 1-99 for hierarchy
            $table->text('description')->nullable();
            $table->timestamps();
        });

        // Add role_id to users table
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('role_id')->nullable()->after('id')->constrained()->nullOnDelete();
            $table->json('permissions')->nullable()->after('role_id'); // Custom permissions per user
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['role_id']);
            $table->dropColumn(['role_id', 'permissions']);
        });
        
        Schema::dropIfExists('roles');
    }
};
