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
        // Trip reports (post-travel documentation)
        Schema::create('trip_reports', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('spd_id')->unique();
            $table->uuid('employee_id');
            
            // Actual travel dates
            $table->date('actual_departure_date');
            $table->date('actual_return_date');
            $table->integer('actual_duration');
            
            // Attachments (stored as JSON array of file paths)
            $table->json('attachments')->nullable();
            
            // Verification
            $table->boolean('is_verified')->default(false);
            $table->string('verified_by')->nullable();
            $table->timestamp('verified_at')->nullable();
            $table->text('verification_notes')->nullable();
            
            $table->timestamp('submitted_at')->nullable();
            $table->timestamps();

            $table->foreign('spd_id')->references('id')->on('spds')->onDelete('cascade');
            $table->foreign('employee_id')->references('id')->on('employees')->onDelete('cascade');
        });

        // Trip activities (daily log)
        Schema::create('trip_activities', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('report_id');
            $table->date('date');
            $table->string('time'); // e.g., "Pukul 13.00 - 15.00 WIB"
            $table->string('location');
            $table->text('description');
            $table->integer('order')->default(0);
            $table->timestamps();

            $table->foreign('report_id')->references('id')->on('trip_reports')->onDelete('cascade');
        });

        // Trip outputs (results/achievements)
        Schema::create('trip_outputs', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('report_id');
            $table->text('description');
            $table->integer('order')->default(0);
            $table->timestamps();

            $table->foreign('report_id')->references('id')->on('trip_reports')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('trip_outputs');
        Schema::dropIfExists('trip_activities');
        Schema::dropIfExists('trip_reports');
    }
};
