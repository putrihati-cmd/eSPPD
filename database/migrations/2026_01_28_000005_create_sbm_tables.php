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
        // SBM Settings (Standar Biaya Masukan) configuration
        Schema::create('sbm_settings', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('organization_id');
            $table->integer('year');
            $table->string('pmk_number'); // e.g., "PMK 32/2025"
            $table->date('effective_date');
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->foreign('organization_id')->references('id')->on('organizations')->onDelete('cascade');
        });

        // Daily Allowance rates by province
        Schema::create('daily_allowances', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('sbm_setting_id');
            $table->string('province');
            $table->enum('category', ['luar_kota', 'dalam_kota', 'diklat'])->default('luar_kota');
            $table->decimal('amount', 12, 2);
            $table->decimal('representation_amount', 12, 2)->nullable();
            $table->timestamps();

            $table->foreign('sbm_setting_id')->references('id')->on('sbm_settings')->onDelete('cascade');
        });

        // Accommodation rates by province and grade
        Schema::create('accommodations', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('sbm_setting_id');
            $table->string('province');
            $table->string('grade_level'); // eselon_1, eselon_2, golongan_III, golongan_IV
            $table->decimal('max_amount', 12, 2);
            $table->timestamps();

            $table->foreign('sbm_setting_id')->references('id')->on('sbm_settings')->onDelete('cascade');
        });

        // Transportation rates
        Schema::create('transportations', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('sbm_setting_id');
            $table->enum('type', ['pesawat', 'kereta', 'bus', 'kapal', 'taxi'])->default('pesawat');
            $table->string('route')->nullable();
            $table->decimal('max_amount', 12, 2)->nullable();
            $table->boolean('is_riil')->default(false);
            $table->timestamps();

            $table->foreign('sbm_setting_id')->references('id')->on('sbm_settings')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transportations');
        Schema::dropIfExists('accommodations');
        Schema::dropIfExists('daily_allowances');
        Schema::dropIfExists('sbm_settings');
    }
};
