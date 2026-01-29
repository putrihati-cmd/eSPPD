<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Scheduled reports for automatic delivery
        if (!Schema::hasTable('scheduled_reports')) {
            Schema::create('scheduled_reports', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->string('name');
                $table->string('type')->default('sppd_list');
                $table->enum('frequency', ['daily', 'weekly', 'monthly'])->default('weekly');
                $table->json('filters')->nullable();
                $table->json('recipients'); // array of emails
                $table->boolean('is_active')->default(true);
                $table->timestamp('last_run_at')->nullable();
                $table->timestamp('next_run_at')->nullable();
                $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();
            });
        }

        // Add reminder and escalation tracking to approvals
        if (Schema::hasTable('approvals')) {
            $hasRemindedAt = Schema::hasColumn('approvals', 'reminded_at');
            $hasEscalatedAt = Schema::hasColumn('approvals', 'escalated_at');

            if (!$hasRemindedAt || !$hasEscalatedAt) {
                Schema::table('approvals', function (Blueprint $table) use ($hasRemindedAt, $hasEscalatedAt) {
                    if (!$hasRemindedAt) {
                        $table->timestamp('reminded_at')->nullable();
                    }
                    if (!$hasEscalatedAt) {
                        $table->timestamp('escalated_at')->nullable();
                    }
                });
            }
        }
    }



    public function down(): void
    {
        Schema::dropIfExists('scheduled_reports');
        
        Schema::table('approvals', function (Blueprint $table) {
            $table->dropColumn(['reminded_at', 'escalated_at']);
        });
    }
};
