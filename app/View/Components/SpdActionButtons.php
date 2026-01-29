<?php

namespace App\View\Components;

use Illuminate\View\Component;
use App\Models\Spd;

/**
 * btn.md Implementation: SPD Action Buttons Component
 * Renders conditional buttons based on status and user role
 */
class SpdActionButtons extends Component
{
    public Spd $spd;
    public $user;
    public bool $showDetail = true;
    public bool $showEdit = false;
    public bool $showCancel = false;
    public bool $showDownloadSt = false;
    public bool $showDownloadSpd = false;
    public bool $showInputLpj = false;
    public bool $showViewLpj = false;
    public bool $showApprove = false;
    public bool $showReject = false;
    public bool $showOverrideCancel = false;

    public function __construct(Spd $spd)
    {
        $this->spd = $spd;
        $this->user = auth()->user();
        $this->determineButtonVisibility();
    }

    /**
     * btn.md: Determine which buttons to show based on status and role
     */
    protected function determineButtonVisibility(): void
    {
        $status = $this->spd->status;
        $isOwner = $this->user->employee_id === $this->spd->employee_id;
        $isCurrentApprover = $this->spd->current_approver_nip === $this->user->employee?->nip;
        $roleLevel = $this->user->role_level ?? 1;

        // btn-detail: Always visible
        $this->showDetail = true;

        // btn-edit: Only draft, only owner
        $this->showEdit = $status === 'draft' && $isOwner;

        // btn-cancel: draft/submitted, owner OR override level
        $this->showCancel = in_array($status, ['draft', 'submitted']) && 
                           ($isOwner || $this->user->canOverride());

        // btn-download-st: Only approved
        $this->showDownloadSt = $status === 'approved';

        // btn-download-spd: Only approved
        $this->showDownloadSpd = $status === 'approved';

        // btn-input-lpj: approved AND no LPJ yet
        $this->showInputLpj = $status === 'approved' && 
                              !$this->spd->report()->exists() &&
                              $isOwner;

        // btn-view-lpj: has LPJ
        $this->showViewLpj = $this->spd->report()->exists();

        // btn-approve/reject: pending AND user is current approver
        $this->showApprove = in_array($status, ['submitted', 'pending_approval']) && 
                            $isCurrentApprover;
        $this->showReject = $this->showApprove;

        // btn-override-cancel: Dekan+ can force cancel any status
        $this->showOverrideCancel = $this->user->canOverride() && 
                                    !in_array($status, ['completed', 'cancelled']);
    }

    public function render()
    {
        return view('components.spd-action-buttons');
    }
}
