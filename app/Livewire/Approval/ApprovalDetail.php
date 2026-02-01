<?php

namespace App\Livewire\Approval;

use App\Models\Sppd;
use App\Models\SppdApproval;
use App\Services\AnggaranService;
use Livewire\Component;
use Livewire\Attributes\Layout;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

#[Layout('layouts.app')]
class ApprovalDetail extends Component
{
    public Sppd $sppd;
    public string $rejectionReason = '';
    public bool $showRejectModal = false;

    public function mount(Sppd $sppd)
    {
        $this->sppd = $sppd;
        if ($sppd->current_approver_nip !== Auth::user()->employee->nip) {
            abort(403, 'Anda bukan pemegang hak approval untuk SPPD ini.');
        }
    }

    public function approve()
    {
        $user = Auth::user();
        $employee = $user->employee;
        try {
            DB::transaction(function () use ($employee) {
                // Approval logic, update status, next approver, etc.
            });
            return redirect()->route('approver.dashboard');
        } catch (\Exception $e) {
            Log::error('Approval Error: ' . $e->getMessage());
            $this->addError('general', $e->getMessage());
        }
    }

    public function reject()
    {
        $this->validate(['rejectionReason' => 'required|string|min:10']);
        $user = Auth::user();
        DB::transaction(function () use ($user) {
            SppdApproval::create([
                // rejection logic
            ]);
        });
        session()->flash('success', 'SPPD ditolak.');
        return redirect()->route('approver.dashboard');
    }

    public function render()
    {
        return view('livewire.approval.detail');
    }
}
