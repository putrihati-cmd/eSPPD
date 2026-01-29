<?php

namespace App\Http\Controllers;

use App\Models\Spd;
use App\Models\Approval;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Controller untuk menangani revisi SPPD yang ditolak
 * 
 * Flow:
 * 1. SPPD ditolak oleh atasan
 * 2. Pegawai melihat alasan penolakan
 * 3. Pegawai mengedit SPPD
 * 4. Pegawai submit ulang (resubmit)
 * 5. SPPD kembali ke queue approval
 */
class SppdRevisionController extends Controller
{
    /**
     * Menampilkan form edit untuk SPPD yang ditolak
     *
     * @param Spd $spd
     * @return \Illuminate\View\View
     */
    public function editRejected(Spd $spd)
    {
        // Validasi: hanya pemilik yang bisa edit
        $user = Auth::user();
        $employee = $user->employee;
        
        if (!$employee || $spd->employee_id !== $employee->id) {
            abort(403, 'Anda tidak memiliki akses untuk mengedit SPPD ini.');
        }
        
        // Validasi: hanya yang rejected yang bisa diedit untuk revisi
        if ($spd->status !== 'rejected') {
            abort(400, 'SPPD ini tidak dalam status ditolak.');
        }
        
        // Load history revisi
        $revisionHistory = $spd->revision_history ? json_decode($spd->revision_history, true) : [];
        
        return view('spd.revisi', [
            'spd' => $spd->load(['employee', 'costs', 'followers', 'approvals']),
            'revisionHistory' => $revisionHistory,
            'rejectionReason' => $spd->rejection_reason,
        ]);
    }
    
    /**
     * Simpan revisi dan ajukan ulang SPPD
     *
     * @param Request $request
     * @param Spd $spd
     * @return \Illuminate\Http\RedirectResponse
     */
    public function resubmit(Request $request, Spd $spd)
    {
        // Validasi: hanya pemilik yang bisa resubmit
        $user = Auth::user();
        $employee = $user->employee;
        
        if (!$employee || $spd->employee_id !== $employee->id) {
            abort(403, 'Anda tidak memiliki akses untuk mengajukan ulang SPPD ini.');
        }
        
        // Validasi: hanya yang rejected yang bisa diresubmit
        if ($spd->status !== 'rejected') {
            abort(400, 'Hanya SPPD yang ditolak yang bisa diajukan ulang.');
        }
        
        // Validasi input
        $validated = $request->validate([
            'destination' => 'sometimes|required|string|max:255',
            'purpose' => 'sometimes|required|string',
            'departure_date' => 'sometimes|required|date|after_or_equal:today',
            'return_date' => 'sometimes|required|date|after_or_equal:departure_date',
            'revision_notes' => 'required|string|min:10|max:1000',
        ]);
        
        DB::beginTransaction();
        try {
            // Simpan history revisi
            $currentHistory = $spd->revision_history ? json_decode($spd->revision_history, true) : [];
            $currentHistory[] = [
                'version' => $spd->revision_count + 1,
                'revised_at' => now()->toISOString(),
                'revised_by' => $employee->nip,
                'notes' => $validated['revision_notes'],
                'previous_rejection_reason' => $spd->rejection_reason,
                'changes' => array_diff_assoc($validated, $spd->toArray()),
            ];
            
            // Cari atasan untuk dikirimi ulang
            // Gunakan previous_approver_nip jika ada, atau cari dari hierarchy
            $nextApproverNip = $spd->previous_approver_nip ?? $this->getNextApprover($employee);
            
            // Update SPPD
            $updateData = [
                'status' => 'submitted',
                'current_approver_nip' => $nextApproverNip,
                'revision_count' => $spd->revision_count + 1,
                'revision_history' => json_encode($currentHistory),
                'rejection_reason' => null, // Clear rejection reason
                'rejected_at' => null,
                'rejected_by' => null,
                'submitted_at' => now(),
            ];
            
            // Tambahkan field yang diubah
            if (isset($validated['destination'])) {
                $updateData['destination'] = $validated['destination'];
            }
            if (isset($validated['purpose'])) {
                $updateData['purpose'] = $validated['purpose'];
            }
            if (isset($validated['departure_date']) && isset($validated['return_date'])) {
                $updateData['departure_date'] = $validated['departure_date'];
                $updateData['return_date'] = $validated['return_date'];
                $updateData['duration'] = now()->parse($validated['departure_date'])
                    ->diffInDays(now()->parse($validated['return_date'])) + 1;
            }
            
            $spd->update($updateData);
            
            // Buat approval record baru
            Approval::create([
                'spd_id' => $spd->id,
                'approver_id' => null, // Akan diisi nanti
                'approver_nip' => $nextApproverNip,
                'level' => 1, // Start from level 1 again
                'status' => 'pending',
                'notes' => 'Resubmitted after revision #' . $spd->revision_count,
            ]);
            
            DB::commit();
            
            Log::info("SPPD {$spd->id} resubmitted by {$employee->nip} (revision #{$spd->revision_count})");
            
            return redirect()
                ->route('spd.show', $spd)
                ->with('success', 'SPPD berhasil diajukan ulang. Menunggu persetujuan atasan.');
                
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Failed to resubmit SPPD {$spd->id}: " . $e->getMessage());
            
            return back()
                ->withErrors(['error' => 'Gagal mengajukan ulang SPPD. Silakan coba lagi.'])
                ->withInput();
        }
    }
    
    /**
     * Menampilkan riwayat revisi SPPD
     *
     * @param Spd $spd
     * @return \Illuminate\View\View
     */
    public function history(Spd $spd)
    {
        $user = Auth::user();
        $employee = $user->employee;
        
        // Validasi akses: pemilik, approver, atau admin
        $canView = false;
        
        if ($employee && $spd->employee_id === $employee->id) {
            $canView = true;
        }
        
        if ($user->role === 'admin' || $user->role === 'superadmin') {
            $canView = true;
        }
        
        // Check if user is approver in the chain
        if ($spd->approvals()->where('approver_nip', $employee?->nip)->exists()) {
            $canView = true;
        }
        
        if (!$canView) {
            abort(403, 'Anda tidak memiliki akses untuk melihat riwayat revisi ini.');
        }
        
        $revisionHistory = $spd->revision_history ? json_decode($spd->revision_history, true) : [];
        
        return view('spd.history', [
            'spd' => $spd->load(['employee', 'approvals.approver']),
            'revisionHistory' => $revisionHistory,
        ]);
    }
    
    /**
     * Mendapatkan NIP atasan untuk approval
     *
     * @param \App\Models\Employee $employee
     * @return string|null
     */
    private function getNextApprover($employee): ?string
    {
        // Cari dari unit head
        $unit = $employee->unit;
        if ($unit && $unit->head_employee_id && $unit->head_employee_id !== $employee->id) {
            $head = $unit->headEmployee;
            if ($head) {
                return $head->nip;
            }
        }
        
        // Fallback: cari user dengan role approver di unit yang sama
        $approver = \App\Models\User::where('role', 'approver')
            ->whereHas('employee', function ($q) use ($employee) {
                $q->where('unit_id', $employee->unit_id)
                  ->where('id', '!=', $employee->id);
            })
            ->first();
            
        return $approver?->employee?->nip;
    }
}
