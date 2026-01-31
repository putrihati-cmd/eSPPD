<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('permissions', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('label');
            $table->text('description')->nullable();
            $table->string('category')->default('general'); // spd, approval, finance, admin, etc
            $table->timestamps();
        });

        Schema::create('role_permissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('role_id')->constrained()->onDelete('cascade');
            $table->foreignId('permission_id')->constrained()->onDelete('cascade');
            $table->timestamps();

            $table->unique(['role_id', 'permission_id']);
        });

        Schema::create('user_permissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('permission_id')->constrained()->onDelete('cascade');
            $table->timestamps();

            $table->unique(['user_id', 'permission_id']);
        });

        // Approval delegations
        Schema::create('approval_delegations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('delegator_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('delegate_id')->constrained('users')->onDelete('cascade');
            $table->string('reason')->nullable();
            $table->dateTime('valid_from');
            $table->dateTime('valid_until')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['delegator_id', 'delegate_id']);
            $table->index(['valid_from', 'valid_until']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('approval_delegations');
        Schema::dropIfExists('user_permissions');
        Schema::dropIfExists('role_permissions');
        Schema::dropIfExists('permissions');
    }
};
