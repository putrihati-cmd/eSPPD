<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('trip_report_versions')) {
            Schema::create('trip_report_versions', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->foreignUuid('trip_report_id')->constrained()->cascadeOnDelete();
                $table->integer('version_number');
                $table->json('content')->nullable();
                $table->string('changes_summary')->nullable();
                $table->foreignId('changed_by')->nullable()->constrained('users')->nullOnDelete();
                $table->string('file_path')->nullable();
                $table->timestamps();

                $table->unique(['trip_report_id', 'version_number']);
            });
        }
    }


    public function down(): void
    {
        Schema::dropIfExists('trip_report_versions');
    }
};
