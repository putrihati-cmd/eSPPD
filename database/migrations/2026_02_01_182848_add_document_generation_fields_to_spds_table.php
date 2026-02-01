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
            $table->timestamp('spt_generated_at')->nullable()->after('completed_at');
            $table->timestamp('spd_generated_at')->nullable()->after('spt_generated_at');
            $table->string('spt_file_path')->nullable()->after('spd_generated_at');
            $table->string('spd_file_path')->nullable()->after('spt_file_path');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('spds', function (Blueprint $table) {
            $table->dropColumn([
                'spt_generated_at',
                'spd_generated_at',
                'spt_file_path',
                'spd_file_path'
            ]);
        });
    }
};
