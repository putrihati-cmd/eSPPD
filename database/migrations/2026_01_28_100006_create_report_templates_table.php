<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('report_templates')) {
            Schema::create('report_templates', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->string('name');
                $table->enum('type', ['trip_report', 'sppd', 'spt'])->default('trip_report');
                $table->string('file_path');
                $table->boolean('is_default')->default(false);
                $table->foreignUuid('uploaded_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();

                $table->index(['type', 'is_default']);
            });
        }
    }


    public function down(): void
    {
        Schema::dropIfExists('report_templates');
    }
};
