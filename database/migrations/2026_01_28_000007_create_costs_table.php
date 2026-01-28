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
        Schema::create('costs', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('spd_id');
            $table->enum('category', ['uang_harian', 'penginapan', 'transport', 'representasi', 'lainnya'])->default('uang_harian');
            $table->string('description');
            $table->decimal('estimated_amount', 12, 2);
            $table->decimal('actual_amount', 12, 2)->nullable();
            $table->string('receipt_file')->nullable();
            $table->string('receipt_number')->nullable();
            $table->date('receipt_date')->nullable();
            $table->decimal('sbm_max_amount', 12, 2)->nullable();
            $table->boolean('exceeds_sbm')->default(false);
            $table->timestamps();

            $table->foreign('spd_id')->references('id')->on('spds')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('costs');
    }
};
