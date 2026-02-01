<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * LOGIC MAP: Approval Level Hierarchy (Source of Truth for Employee Hierarchy)
     * 1 = Staff/Dosen
     * 2 = Kepala Prodi
     * 3 = Wakil Dekan
     * 4 = Dekan
     * 5 = Wakil Rektor
     * 6 = Rektor
     */
    public function up(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            // Add approval_level (hierarchy 1-6) if not exists
            if (!Schema::hasColumn('employees', 'approval_level')) {
                $table->tinyInteger('approval_level')
                    ->default(1)
                    ->comment('Hierarchy level (1-6): 1=Staff/Dosen, 2=Kaprodi, 3=Wadek, 4=Dekan, 5=Warek, 6=Rektor')
                    ->after('employment_status');
            }

            // Add superior_nip for organizational hierarchy
            if (!Schema::hasColumn('employees', 'superior_nip')) {
                $table->string('superior_nip')
                    ->nullable()
                    ->comment('NIP atasan langsung untuk approval chain')
                    ->after('approval_level');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->dropColumn(['approval_level', 'superior_nip']);
        });
    }
};
