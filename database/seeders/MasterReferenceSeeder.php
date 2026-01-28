<?php

namespace Database\Seeders;

use App\Models\GradeReference;
use App\Models\TransportReference;
use App\Models\DestinationReference;
use Illuminate\Database\Seeder;

class MasterReferenceSeeder extends Seeder
{
    public function run(): void
    {
        // Grade Biaya Perjalanan Dinas
        $grades = [
            ['grade_name' => 'Eselon I', 'uang_harian' => 750000, 'uang_representasi' => 250000, 'uang_transport_lokal' => 150000],
            ['grade_name' => 'Eselon II', 'uang_harian' => 650000, 'uang_representasi' => 200000, 'uang_transport_lokal' => 125000],
            ['grade_name' => 'Eselon III', 'uang_harian' => 550000, 'uang_representasi' => 150000, 'uang_transport_lokal' => 100000],
            ['grade_name' => 'Eselon IV', 'uang_harian' => 480000, 'uang_representasi' => 100000, 'uang_transport_lokal' => 75000],
            ['grade_name' => 'Golongan IV', 'uang_harian' => 430000, 'uang_representasi' => 0, 'uang_transport_lokal' => 60000],
            ['grade_name' => 'Golongan III', 'uang_harian' => 380000, 'uang_representasi' => 0, 'uang_transport_lokal' => 50000],
            ['grade_name' => 'Golongan II', 'uang_harian' => 330000, 'uang_representasi' => 0, 'uang_transport_lokal' => 40000],
            ['grade_name' => 'Golongan I', 'uang_harian' => 280000, 'uang_representasi' => 0, 'uang_transport_lokal' => 30000],
        ];

        foreach ($grades as $grade) {
            GradeReference::create($grade);
        }

        // Transportasi
        $transports = [
            ['jenis' => 'pesawat', 'rate_per_km' => 0, 'biaya_tetap' => 0, 'keterangan' => 'Biaya sesuai tiket aktual'],
            ['jenis' => 'kereta', 'rate_per_km' => 500, 'biaya_tetap' => 50000, 'keterangan' => 'Kereta api eksekutif/bisnis'],
            ['jenis' => 'bus', 'rate_per_km' => 300, 'biaya_tetap' => 25000, 'keterangan' => 'Bus AKAP/antar kota'],
            ['jenis' => 'mobil_dinas', 'rate_per_km' => 1500, 'biaya_tetap' => 0, 'keterangan' => 'BBM + tol aktual'],
            ['jenis' => 'kapal', 'rate_per_km' => 0, 'biaya_tetap' => 0, 'keterangan' => 'Biaya sesuai tiket aktual'],
        ];

        foreach ($transports as $transport) {
            TransportReference::create($transport);
        }

        // Tujuan Populer
        $destinations = [
            ['kota' => 'Jakarta', 'provinsi' => 'DKI Jakarta', 'jarak_km' => 0, 'akomodasi_rate' => 850000],
            ['kota' => 'Surabaya', 'provinsi' => 'Jawa Timur', 'jarak_km' => 780, 'akomodasi_rate' => 700000],
            ['kota' => 'Bandung', 'provinsi' => 'Jawa Barat', 'jarak_km' => 150, 'akomodasi_rate' => 650000],
            ['kota' => 'Semarang', 'provinsi' => 'Jawa Tengah', 'jarak_km' => 450, 'akomodasi_rate' => 600000],
            ['kota' => 'Yogyakarta', 'provinsi' => 'DIY', 'jarak_km' => 520, 'akomodasi_rate' => 600000],
            ['kota' => 'Medan', 'provinsi' => 'Sumatera Utara', 'jarak_km' => 1800, 'akomodasi_rate' => 700000],
            ['kota' => 'Makassar', 'provinsi' => 'Sulawesi Selatan', 'jarak_km' => 1600, 'akomodasi_rate' => 650000],
            ['kota' => 'Denpasar', 'provinsi' => 'Bali', 'jarak_km' => 950, 'akomodasi_rate' => 800000],
            ['kota' => 'Palembang', 'provinsi' => 'Sumatera Selatan', 'jarak_km' => 600, 'akomodasi_rate' => 600000],
            ['kota' => 'Balikpapan', 'provinsi' => 'Kalimantan Timur', 'jarak_km' => 1500, 'akomodasi_rate' => 750000],
            ['kota' => 'Purwokerto', 'provinsi' => 'Jawa Tengah', 'jarak_km' => 300, 'akomodasi_rate' => 500000],
            ['kota' => 'Solo', 'provinsi' => 'Jawa Tengah', 'jarak_km' => 540, 'akomodasi_rate' => 550000],
        ];

        foreach ($destinations as $destination) {
            DestinationReference::create($destination);
        }
    }
}
