<?php

/**
 * Konfigurasi eSPPD (Elektronik Surat Perintah Perjalanan Dinas)
 * 
 * Konfigurasi ini mengatur format nomor surat, kode unit, dan setting lainnya
 * sesuai standar Kemendikbud.
 */

return [
    /*
    |--------------------------------------------------------------------------
    | Default Unit Code
    |--------------------------------------------------------------------------
    |
    | Kode unit default untuk nomor surat. Untuk UIN biasanya menggunakan
    | format Un.XX dimana XX adalah nomor institusi.
    |
    */
    'unit_default' => env('ESPPD_UNIT', 'Un.19'),

    /*
    |--------------------------------------------------------------------------
    | Default Kode Bagian
    |--------------------------------------------------------------------------
    |
    | Kode bagian default untuk nomor surat. K.AUPK adalah kode standar
    | untuk bagian umum. Bisa diubah per unit kerja.
    |
    */
    'kode_bagian_default' => env('ESPPD_KODE_BAGIAN', 'K.AUPK'),

    /*
    |--------------------------------------------------------------------------
    | Nama Institusi
    |--------------------------------------------------------------------------
    |
    | Nama lengkap institusi untuk dokumen resmi.
    |
    */
    'nama_institusi' => env('ESPPD_NAMA_INSTITUSI', 'Universitas Islam Negeri Kiai Haji Achmad Siddiq'),
    
    /*
    |--------------------------------------------------------------------------
    | Kota Default
    |--------------------------------------------------------------------------
    |
    | Kota untuk penandatanganan dokumen.
    |
    */
    'kota_default' => env('ESPPD_KOTA', 'Jember'),

    /*
    |--------------------------------------------------------------------------
    | Unit Kerja Mapping
    |--------------------------------------------------------------------------
    |
    | Pemetaan unit kerja ke kode bagian masing-masing.
    | Ketika generate nomor surat, sistem akan mencocokkan nama unit kerja
    | dengan key di mapping ini.
    |
    */
    'units' => [
        'rektorat' => [
            'kode' => 'Un.19',
            'bagian' => 'K.AUPK',
            'nama' => 'Rektorat',
        ],
        'ftik' => [
            'kode' => 'Un.19',
            'bagian' => 'K.AUPK.FTIK',
            'nama' => 'Fakultas Tarbiyah dan Ilmu Keguruan',
        ],
        'febi' => [
            'kode' => 'Un.19',
            'bagian' => 'K.AUPK.FEBI',
            'nama' => 'Fakultas Ekonomi dan Bisnis Islam',
        ],
        'fsh' => [
            'kode' => 'Un.19',
            'bagian' => 'K.AUPK.FSH',
            'nama' => 'Fakultas Syariah',
        ],
        'fuad' => [
            'kode' => 'Un.19',
            'bagian' => 'K.AUPK.FUAD',
            'nama' => 'Fakultas Ushuluddin, Adab, dan Dakwah',
        ],
        'pascasarjana' => [
            'kode' => 'Un.19',
            'bagian' => 'K.AUPK.PS',
            'nama' => 'Pascasarjana',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Approval Level Settings
    |--------------------------------------------------------------------------
    |
    | Konfigurasi level approval berdasarkan jenis perjalanan.
    | luar_negeri membutuhkan approval tertinggi (Rektor).
    |
    */
    'approval_levels' => [
        'luar_negeri' => 6, // Rektor
        'luar_kota' => 4,   // Dekan
        'dalam_kota' => 3,  // Wakil Dekan
    ],

    /*
    |--------------------------------------------------------------------------
    | Document Storage
    |--------------------------------------------------------------------------
    |
    | Konfigurasi penyimpanan dokumen yang dihasilkan.
    |
    */
    'storage' => [
        'disk' => env('ESPPD_STORAGE_DISK', 'local'),
        'path' => 'spd-documents',
        'max_file_size' => 10 * 1024 * 1024, // 10MB
        'allowed_extensions' => ['pdf', 'docx', 'jpg', 'jpeg', 'png'],
    ],

    /*
    |--------------------------------------------------------------------------
    | Python Service
    |--------------------------------------------------------------------------
    |
    | Konfigurasi untuk Python microservice yang menangani
    | document generation, Excel import, dan hierarchy builder.
    |
    */
    'python_service' => [
        'url' => env('PYTHON_SERVICE_URL', 'http://127.0.0.1:8001'),
        'timeout' => 30,
        'retry_attempts' => 3,
    ],
];
