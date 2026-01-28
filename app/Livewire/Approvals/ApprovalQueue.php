<?php

namespace App\Livewire\Approvals;

use App\Models\Approval;
use App\Models\ApprovalDelegate;
use App\Models\Employee;
use App\Services\ApprovalService;
use Livewire\Component;
use Livewire\WithPagination;

class ApprovalQueue extends Component
{
    use WithPagination;

    public string $search = '';
    public array $selectedIds = [];
    public bool $showDelegateModal = false;
    public string $delegateEmployeeId = '';
    public string $delegateStartDate = '';
    public string $delegateEndDate = '';
    public string $delegateReason = '';
    public string $bulkNotes = '';

    protected ApprovalService $approvalService;

    public function boot(ApprovalService $approvalService)
    {
        $this->approvalService = $approvalService;
    }

    public function render()
    {
        $user = auth()->user();
        $employeeId = $user->employee_id;

        // Check if current user has delegate rights
        $delegateFor = ApprovalDelegate::where('delegate_id', $employeeId)
            ->active()
            ->pluck('delegator_id')
            ->toArray();

        $approverIds = array_merge([$employeeId], $delegateFor);

        $pendingApprovals = Approval::with(['spd.employee', 'spd.unit'])
            ->whereIn('approver_id', $approverIds)
            ->where('status', 'pending')
            ->when($this->search, function ($query) {
                $query->whereHas('spd', function ($q) {
                    $q->where('spd_number', 'like', "%{$this->search}%")
                      ->orWhere('destination', 'like', "%{$this->search}%");
                });
            })
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        // My delegates
        $myDelegates = ApprovalDelegate::with('delegate')
            ->where('delegator_id', $employeeId)
            ->orderBy('created_at', 'desc')
            ->get();

        // Available employees for delegation
        $employees = Employee::where('id', '!=', $employeeId)
            ->orderBy('name')
            ->get();

        return view('livewire.approvals.approval-queue', [
            'pendingApprovals' => $pendingApprovals,
            'myDelegates' => $myDelegates,
            'employees' => $employees,
        ])->layout('layouts.app', ['header' => 'Antrian Approval']);
    }

    public function approve($approvalId, $notes = null)
    {
        $approval = Approval::findOrFail($approvalId);
        $this->approvalService->process($approval->spd, 'approve', $notes);
        session()->flash('success', 'SPPD berhasil disetujui.');
    }

    public function reject($approvalId, $notes)
    {
        if (strlen($notes) < 10) {
            session()->flash('error', 'Alasan penolakan minimal 10 karakter.');
            return;
        }

        $approval = Approval::findOrFail($approvalId);
        $this->approvalService->process($approval->spd, 'reject', $notes);
        session()->flash('success', 'SPPD berhasil ditolak.');
    }

    public function bulkApprove()
    {
        if (empty($this->selectedIds)) {
            session()->flash('error', 'Pilih minimal 1 SPPD untuk approval.');
            return;
        }

        $spdIds = Approval::whereIn('id', $this->selectedIds)->pluck('spd_id')->toArray();
        $results = $this->approvalService->bulkApprove($spdIds, auth()->user()->employee_id, $this->bulkNotes);

        $this->selectedIds = [];
        $this->bulkNotes = '';
        
        session()->flash('success', "Berhasil approve {$results['success']} SPPD. Gagal: {$results['failed']}");
    }

    public function createDelegate()
    {
        $this->validate([
            'delegateEmployeeId' => 'required|exists:employees,id',
            'delegateStartDate' => 'required|date|after_or_equal:today',
            'delegateEndDate' => 'required|date|after_or_equal:delegateStartDate',
            'delegateReason' => 'required|string|min:5',
        ]);

        ApprovalDelegate::create([
            'delegator_id' => auth()->user()->employee_id,
            'delegate_id' => $this->delegateEmployeeId,
            'start_date' => $this->delegateStartDate,
            'end_date' => $this->delegateEndDate,
            'reason' => $this->delegateReason,
            'is_active' => true,
        ]);

        $this->reset(['delegateEmployeeId', 'delegateStartDate', 'delegateEndDate', 'delegateReason']);
        $this->showDelegateModal = false;
        session()->flash('success', 'Delegasi berhasil dibuat.');
    }

    public function deactivateDelegate($delegateId)
    {
        ApprovalDelegate::where('id', $delegateId)
            ->where('delegator_id', auth()->user()->employee_id)
            ->update(['is_active' => false]);

        session()->flash('success', 'Delegasi berhasil dinonaktifkan.');
    }
}
