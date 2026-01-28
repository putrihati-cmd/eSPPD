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
        Schema::create('spds', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('organization_id');
            $table->uuid('unit_id');
            $table->uuid('employee_id');
            
            // Document numbers
            $table->string('spt_number')->unique(); // Surat Perintah Tugas
            $table->string('spd_number')->unique(); // Surat Perjalanan Dinas
            
            // Travel details
            $table->string('destination'); // Tujuan
            $table->text('purpose'); // Maksud perjalanan
            $table->string('invitation_number')->nullable();
            $table->string('invitation_file')->nullable();
            
            // Dates
            $table->date('departure_date');
            $table->date('return_date');
            $table->integer('duration'); // Calculated days
            
            // Budget
            $table->uuid('budget_id')->nullable();
            $table->decimal('estimated_cost', 15, 2);
            $table->decimal('actual_cost', 15, 2)->nullable();
            
            // Transportation
            $table->enum('transport_type', ['pesawat', 'kereta', 'bus', 'kapal', 'mobil_dinas'])->default('pesawat');
            $table->boolean('needs_accommodation')->default(true);
            
            // Status
            $table->enum('status', ['draft', 'submitted', 'approved', 'rejected', 'completed'])->default('draft');
            
            // Metadata
            $table->string('created_by');
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->foreign('organization_id')->references('id')->on('organizations')->onDelete('cascade');
            $table->foreign('unit_id')->references('id')->on('units')->onDelete('cascade');
            $table->foreign('employee_id')->references('id')->on('employees')->onDelete('cascade');
            $table->foreign('budget_id')->references('id')->on('budgets')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('spds');
    }
};
