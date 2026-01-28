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
        Schema::create('employees', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('organization_id');
            $table->uuid('unit_id');
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null');
            
            // Personal info
            $table->string('nip')->unique();
            $table->string('name');
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->string('photo')->nullable();
            
            // Employment info
            $table->string('position'); // Jabatan
            $table->string('rank'); // Pangkat
            $table->string('grade'); // Golongan (II/a, III/b, IV/a)
            $table->enum('employment_status', ['PNS', 'PPPK', 'Honorer'])->default('PNS');
            
            // Bank info for reimbursement
            $table->string('bank_name')->nullable();
            $table->string('bank_account')->nullable();
            $table->string('bank_account_name')->nullable();
            
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->foreign('organization_id')->references('id')->on('organizations')->onDelete('cascade');
            $table->foreign('unit_id')->references('id')->on('units')->onDelete('cascade');
        });

        // Add head_employee_id foreign key after employees table is created
        Schema::table('units', function (Blueprint $table) {
            $table->foreign('head_employee_id')->references('id')->on('employees')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('units', function (Blueprint $table) {
            $table->dropForeign(['head_employee_id']);
        });
        Schema::dropIfExists('employees');
    }
};
