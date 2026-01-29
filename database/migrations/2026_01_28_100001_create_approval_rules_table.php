<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('approval_rules', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->text('description')->nullable();
            $table->foreignUuid('organization_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignUuid('unit_id')->nullable()->constrained()->nullOnDelete();
            $table->integer('level')->default(1);
            $table->string('role')->nullable();
            $table->foreignUuid('approver_id')->nullable()->constrained('employees')->nullOnDelete();
            $table->decimal('threshold_amount', 15, 2)->nullable();
            $table->boolean('is_active')->default(true);
            $table->integer('order')->default(0);
            $table->timestamps();
        });

        Schema::create('approval_delegates', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('delegator_id')->constrained('employees')->cascadeOnDelete();
            $table->foreignUuid('delegate_id')->constrained('employees')->cascadeOnDelete();
            $table->date('start_date');
            $table->date('end_date');
            $table->text('reason')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Note: escalated_at and reminded_at columns are added in scheduled_reports migration
        // with proper hasColumn checks to avoid duplicates
    }

    public function down(): void
    {
        Schema::dropIfExists('approval_delegates');
        Schema::dropIfExists('approval_rules');
    }
};
