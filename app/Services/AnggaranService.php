<?php

namespace App\Services;

use App\Models\Anggaran;
use App\Models\Sppd;
use Illuminate\Support\Facades\DB;

class AnggaranService
{
    public function updateRealisasi(Sppd $sppd): array
    {
        return DB::transaction(function () use ($sppd) {
            $anggaran = Anggaran::lockForUpdate()
                ->find($sppd->anggaran_id);
            // Update logic here
            return [
                'status' => 'updated',
            ];
        });
    }
}
