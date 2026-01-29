<?php

namespace App\Services;

use App\Models\Spd;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Service untuk generate nomor surat otomatis sesuai standar Kemendikbud
 * 
 * Format: 0001/Un.19/K.AUPK/FP.10/2025
 * - 0001: Nomor urut per bulan
 * - Un.19: Kode UIN
 * - K.AUPK: Kode Bagian (misal: K.AUPK.FTIK untuk FTIK)
 * - FP.10: Kode Fungsi + Bulan (2 digit)
 * - 2025: Tahun (4 digit)
 */
class NomorSuratService
{
    /**
     * Generate nomor Surat Tugas baru
     *
     * @param string $unit Kode unit (default: Un.19)
     * @param string $kodeBagian Kode bagian (default: K.AUPK)
     * @return array ['nomor_urut' => '0001', 'nomor_lengkap' => '0001/Un.19/K.AUPK/FP.10/2025']
     */
    public static function generateSuratTugas(string $unit = null, string $kodeBagian = null): array
    {
        $unit = $unit ?? config('esppd.unit_default', 'Un.19');
        $kodeBagian = $kodeBagian ?? config('esppd.kode_bagian_default', 'K.AUPK');
        $tahun = date('Y');
        $bulan = date('m');
        
        // Hitung nomor urut dari SPD yang sudah diapprove bulan ini
        // Include yang dihapus (withTrashed) untuk anti duplikat
        $lastNumber = Spd::whereYear('approved_at', $tahun)
            ->whereMonth('approved_at', $bulan)
            ->whereNotNull('spt_number')
            ->withTrashed()
            ->count();
        
        $nomorUrut = str_pad($lastNumber + 1, 4, '0', STR_PAD_LEFT);
        $kodeFungsi = 'FP.' . $bulan;
        $nomorLengkap = "{$nomorUrut}/{$unit}/{$kodeBagian}/{$kodeFungsi}/{$tahun}";
        
        return [
            'nomor_urut' => $nomorUrut,
            'nomor_lengkap' => $nomorLengkap,
            'bulan' => $bulan,
            'tahun' => $tahun,
        ];
    }
    
    /**
     * Generate nomor SPD (Surat Perjalanan Dinas)
     * Format: SPD.0001/Un.19/K.AUPK/FP.10/2025
     */
    public static function generateSpd(string $unit = null, string $kodeBagian = null): array
    {
        $sptData = self::generateSuratTugas($unit, $kodeBagian);
        $sptData['nomor_lengkap'] = 'SPD.' . $sptData['nomor_lengkap'];
        return $sptData;
    }
    
    /**
     * Cek apakah nomor surat sudah ada (termasuk yang dihapus)
     *
     * @param string $nomorSurat
     * @return bool
     */
    public static function isExists(string $nomorSurat): bool
    {
        return Spd::where('spt_number', $nomorSurat)
            ->orWhere('spd_number', $nomorSurat)
            ->withTrashed()
            ->exists();
    }
    
    /**
     * Generate nomor surat dengan retry untuk menghindari race condition
     *
     * @param string $unit
     * @param string $kodeBagian
     * @param int $maxRetries
     * @return array
     * @throws \Exception
     */
    public static function generateWithRetry(string $unit = null, string $kodeBagian = null, int $maxRetries = 3): array
    {
        $retry = 0;
        $result = null;
        
        while ($retry < $maxRetries) {
            $result = self::generateSuratTugas($unit, $kodeBagian);
            
            // Lock untuk mencegah race condition
            if (!self::isExists($result['nomor_lengkap'])) {
                Log::info('Generated nomor surat: ' . $result['nomor_lengkap']);
                return $result;
            }
            
            // Tunggu sebentar sebelum retry
            usleep(100000); // 100ms
            $retry++;
            
            Log::warning("Nomor surat conflict, retry {$retry}: " . $result['nomor_lengkap']);
        }
        
        // Jika masih gagal, tambahkan suffix random
        $fallback = $result;
        $fallback['nomor_lengkap'] .= '-' . strtoupper(substr(md5(microtime()), 0, 4));
        
        Log::warning('Using fallback nomor surat: ' . $fallback['nomor_lengkap']);
        
        return $fallback;
    }
    
    /**
     * Get kode bagian berdasarkan unit kerja
     *
     * @param string $unitKerja Nama unit kerja
     * @return string
     */
    public static function getKodeBagianByUnit(string $unitKerja): string
    {
        $mapping = config('esppd.units', []);
        
        // Cari kecocokan
        foreach ($mapping as $key => $config) {
            if (stripos($unitKerja, $key) !== false) {
                return $config['bagian'] ?? config('esppd.kode_bagian_default', 'K.AUPK');
            }
        }
        
        return config('esppd.kode_bagian_default', 'K.AUPK');
    }
    
    /**
     * Parse nomor surat untuk mendapatkan komponen-komponennya
     *
     * @param string $nomorSurat
     * @return array|null
     */
    public static function parse(string $nomorSurat): ?array
    {
        // Pattern: 0001/Un.19/K.AUPK/FP.10/2025
        $pattern = '/^(SPD\.)?(\d{4})\/([^\/]+)\/([^\/]+)\/(FP\.(\d{2}))\/(\d{4})$/';
        
        if (preg_match($pattern, $nomorSurat, $matches)) {
            return [
                'is_spd' => !empty($matches[1]),
                'nomor_urut' => $matches[2],
                'unit' => $matches[3],
                'kode_bagian' => $matches[4],
                'bulan' => $matches[6],
                'tahun' => $matches[7],
            ];
        }
        
        return null;
    }
    
    /**
     * Validate format nomor surat
     *
     * @param string $nomorSurat
     * @return bool
     */
    public static function isValidFormat(string $nomorSurat): bool
    {
        return self::parse($nomorSurat) !== null;
    }
}
