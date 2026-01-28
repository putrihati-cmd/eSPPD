<?php

return [
    // Laravel Excel Configuration
    'exports' => [
        'chunk_size' => 1000,
        'pre_calculate_formulas' => false,
        'strict_null_comparison' => false,
        'csv' => [
            'delimiter' => ',',
            'enclosure' => '"',
            'line_ending' => PHP_EOL,
            'use_bom' => false,
            'include_separator_line' => false,
            'excel_compatibility' => false,
        ],
        'properties' => [
            'creator' => 'e-SPPD System',
            'lastModifiedBy' => 'e-SPPD System',
            'title' => 'Data Export',
            'description' => 'Data exported from e-SPPD',
            'subject' => 'SPPD Data',
            'keywords' => 'sppd,data,export',
            'category' => 'Data',
            'manager' => 'SPPD Manager',
            'company' => 'Instansi',
        ],
    ],

    'imports' => [
        'read_only' => true,
        'ignore_empty' => false,
        'heading_row' => [
            'formatter' => 'slug',
        ],
        'csv' => [
            'delimiter' => ',',
            'enclosure' => '"',
            'escape_character' => '\\',
            'contiguous' => false,
            'input_encoding' => 'UTF-8',
        ],
        'properties' => [
            'creator' => 'e-SPPD System',
            'lastModifiedBy' => 'e-SPPD System',
        ],
    ],

    'extension_detector' => [
        'xlsx' => \PhpOffice\PhpSpreadsheet\IOFactory::READER_XLSX,
        'xlsm' => \PhpOffice\PhpSpreadsheet\IOFactory::READER_XLSX,
        'xltx' => \PhpOffice\PhpSpreadsheet\IOFactory::READER_XLSX,
        'xltm' => \PhpOffice\PhpSpreadsheet\IOFactory::READER_XLSX,
        'xls' => \PhpOffice\PhpSpreadsheet\IOFactory::READER_XLS,
        'xlt' => \PhpOffice\PhpSpreadsheet\IOFactory::READER_XLS,
        'ods' => \PhpOffice\PhpSpreadsheet\IOFactory::READER_ODS,
        'ots' => \PhpOffice\PhpSpreadsheet\IOFactory::READER_ODS,
        'slk' => \PhpOffice\PhpSpreadsheet\IOFactory::READER_SLK,
        'xml' => \PhpOffice\PhpSpreadsheet\IOFactory::READER_XML,
        'gnumeric' => \PhpOffice\PhpSpreadsheet\IOFactory::READER_GNUMERIC,
        'htm' => \PhpOffice\PhpSpreadsheet\IOFactory::READER_HTML,
        'html' => \PhpOffice\PhpSpreadsheet\IOFactory::READER_HTML,
        'csv' => \PhpOffice\PhpSpreadsheet\IOFactory::READER_CSV,
        'tsv' => \PhpOffice\PhpSpreadsheet\IOFactory::READER_CSV,
    ],

    'value_binder' => [
        'default' => Maatwebsite\Excel\DefaultValueBinder::class,
    ],

    'cache' => [
        'driver' => 'memory',
        'settings' => [
            'memoryCacheSize' => '32MB',
            'dir' => null,
        ],
    ],

    'transactions' => [
        'handler' => 'db',
        'db' => [
            'connection' => null,
        ],
    ],

    'temporary_files' => [
        'local_path' => storage_path('app/excel/temp'),
        'remote_disk' => null,
        'remote_prefix' => null,
        'force_resync_remote' => null,
    ],
];
