<?php

namespace App\Livewire\Sppd;

use App\Models\Anggaran;
use App\Models\Employee;
use App\Models\Sppd;
use App\Models\Delegation;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\Attributes\Layout;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

#[Layout('layouts.app')]
class CreateSppd extends Component
{
    use WithFileUploads;
    public string $destination = '';
    public string $purpose = '';
    public string $start_date = '';
    public string $end_date = '';
    public float $biaya_transport = 0;
    public float $biaya_hotel = 0;
    public float $biaya_harian = 0;
    public $attachment;
    public int $anggaran_id = 0;
    public string $mode = 'draft';
    public float $total_biaya = 0;
    public ?Employee $superior = null;
    protected function rules(): array
    {
        return [
            'destination' => 'required|string|min:3|max:100',
            'purpose' => 'required|string|min:10',
            'start_date' => 'required|date|after_or_equal:today',
            'end_date' => 'required|date|after_or_equal:start_date',
            'biaya_transport' => 'required|numeric|min:0',
            'biaya_hotel' => 'required|numeric|min:0',
            'biaya_harian' => 'required|numeric|min:0',
            'anggaran_id' => 'required|exists:anggarans,id',
            'attachment' => 'nullable|file|max:2048|mimes:pdf,jpg,png',
        ];
    }
    public function mount()
    {
        $this->start_date = now()->format('Y-m-d');
        $this->end_date = now()->addDays(2)->format('Y-m-d');
        $this->loadSuperior();
    }
    public function updated($property)
    {
        if (in_array($property, ['biaya_transport', 'biaya_hotel', 'biaya_harian'])) {
            $this->calculateTotal();
        }
    }
    public function calculateTotal()
    {
        $this->total_biaya = $this->biaya_transport + $this->biaya_hotel + $this->biaya_harian;
    }
    private function loadSuperior()
    {
        $user = Auth::user()->employee;
        $this->superior = $user->superior;
    }
    public function saveAsDraft()
    {
        $this->mode = 'draft';
        $this->createSppd();
    }
    public function submitForApproval()
    {
        $this->mode = 'submit';
        $this->validate();
        if ($this->hasDateOverlap()) {
            $this->addError('start_date', 'Anda memiliki SPPD lain pada rentang tanggal tersebut.');
            return;
        }
        $this->createSppd();
    }
    private function hasDateOverlap(): bool
    {
        $userNip = Auth::user()->employee->nip;
        return Sppd::where('employee_nip', $userNip)
            ->whereNotIn('status', ['rejected', 'draft'])
            ->where(function ($query) {
                $query->whereBetween('start_date', [$this->start_date, $this->end_date]);
            })
            ->exists();
    }
    public function createSppd()
    {
        $validated = $this->validate();
        try {
            $attachmentPath = null;
            if ($this->attachment) {
                $attachmentPath = $this->attachment->store('attachments', 'public');
            }
            $status = $this->mode === 'draft' ? 'draft' : 'pending';
            $currentApprover = null;
            if ($status === 'pending') {
                $currentApprover = $this->determineApprover(Auth::user()->employee);
            }
            $sppd = Sppd::create([
                'employee_nip' => Auth::user()->employee->nip,
                'destination' => $this->destination,
                'purpose' => $this->purpose,
                'start_date' => $this->start_date,
                'end_date' => $this->end_date,
                'biaya_transport' => $this->biaya_transport,
                'biaya_hotel' => $this->biaya_hotel,
                'biaya_harian' => $this->biaya_harian,
                'total_biaya' => $this->total_biaya,
                'status' => $status,
                'current_approver_nip' => $currentApprover,
                'anggaran_id' => $this->anggaran_id,
                'attachment_path' => $attachmentPath,
            ]);
            if ($status === 'pending') {
                return redirect()->route('staff.sppd.show', $sppd);
            } else {
                session()->flash('success', 'Draft SPPD tersimpan.');
                return redirect()->route('staff.sppd.edit', $sppd);
            }
        } catch (\Exception $e) {
            Log::error('SPPD Creation Error: ' . $e->getMessage());
            $this->addError('general', 'Terjadi kesalahan saat menyimpan. Silakan coba lagi.');
        }
    }
    private function determineApprover(Employee $employee): ?string
    {
        $superiorNip = $employee->superior_nip;
        if (!$superiorNip) {
            return null;
        }
        $delegation = Delegation::where('delegator_nip', $superiorNip)
            ->where('start_date', '<=', now())
            ->where('end_date', '>=', now())
            ->where('is_active', true)
            ->first();
        if ($delegation) {
            return $delegation->delegate_nip;
        }
        return $superiorNip;
    }
    public function render()
    {
        $userUnit = Auth::user()->employee->faculty;
        $anggarans = Anggaran::where('unit_kerja', $userUnit)
            ->where('tahun_anggaran', now()->year)
            ->get();
        return view('livewire.sppd.create', [
            'anggarans' => $anggarans,
        ]);
    }
}
