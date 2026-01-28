<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Tabel grade biaya perjalanan dinas
        Schema::create('grade_references', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('grade_name'); // Grade 1, Grade 2, dst
            $table->decimal('uang_harian', 15, 2);
            $table->decimal('uang_representasi', 15, 2)->default(0);
            $table->decimal('uang_transport_lokal', 15, 2)->default(0);
            $table->text('keterangan')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Tabel referensi transportasi
        Schema::create('transport_references', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('jenis'); // pesawat, kereta, bus, mobil_dinas, kapal
            $table->decimal('rate_per_km', 15, 2)->default(0);
            $table->decimal('biaya_tetap', 15, 2)->default(0);
            $table->text('keterangan')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Tabel referensi tujuan
        Schema::create('destination_references', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('kota');
            $table->string('provinsi')->nullable();
            $table->integer('jarak_km')->default(0);
            $table->decimal('akomodasi_rate', 15, 2)->default(0);
            $table->boolean('luar_negeri')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('destination_references');
        Schema::dropIfExists('transport_references');
        Schema::dropIfExists('grade_references');
    }
};
