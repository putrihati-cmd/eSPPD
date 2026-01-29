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
        // Add missing column to budgets table
        if (Schema::hasTable('budgets') && !Schema::hasColumn('budgets', 'available_budget')) {
            Schema::table('budgets', function (Blueprint $table) {
                $table->bigInteger('available_budget')->nullable()->after('used_budget');
            });
        }

        // Add missing column to spds table
        if (Schema::hasTable('spds') && !Schema::hasColumn('spds', 'nomor_sppd')) {
            Schema::table('spds', function (Blueprint $table) {
                $table->string('nomor_sppd')->nullable()->after('spd_number');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop columns if they exist
        if (Schema::hasTable('budgets') && Schema::hasColumn('budgets', 'available_budget')) {
            Schema::table('budgets', function (Blueprint $table) {
                $table->dropColumn('available_budget');
            });
        }

        if (Schema::hasTable('spds') && Schema::hasColumn('spds', 'nomor_sppd')) {
            Schema::table('spds', function (Blueprint $table) {
                $table->dropColumn('nomor_sppd');
            });
        }
    }
};
