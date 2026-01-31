<?php

namespace App\Livewire\Admin;

use App\Models\AuditLog;
use App\Models\User;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Computed;

class AuditLogViewer extends Component
{
    use WithPagination;

    public string $search = '';
    public string $entity = '';
    public string $action = '';
    public ?int $user_id = null;
    public string $date_from = '';
    public string $date_to = '';

    #[Computed]
    public function logs()
    {
        return AuditLog::query()
            ->with('user')
            ->when($this->search, fn($q) =>
                $q->where('entity_id', 'like', "%{$this->search}%")
                  ->orWhereHas('user', fn($sq) =>
                      $sq->where('name', 'like', "%{$this->search}%")
                  )
            )
            ->when($this->entity, fn($q) =>
                $q->where('entity', '=', $this->entity)
            )
            ->when($this->action, fn($q) =>
                $q->where('action', '=', $this->action)
            )
            ->when($this->user_id, fn($q) =>
                $q->where('user_id', '=', $this->user_id)
            )
            ->when($this->date_from, fn($q) =>
                $q->whereDate('created_at', '>=', $this->date_from)
            )
            ->when($this->date_to, fn($q) =>
                $q->whereDate('created_at', '<=', $this->date_to)
            )
            ->orderBy('created_at', 'desc')
            ->paginate(20);
    }

    #[Computed]
    public function users()
    {
        return User::orderBy('name')->get();
    }

    #[Computed]
    public function entities()
    {
        return AuditLog::distinct()->pluck('entity')->sort();
    }

    #[Computed]
    public function actions()
    {
        return AuditLog::distinct()->pluck('action')->sort();
    }

    public function resetFilters(): void
    {
        $this->reset(['search', 'entity', 'action', 'user_id', 'date_from', 'date_to']);
        $this->resetPage();
    }

    public function export()
    {
        $logs = $this->logs();
        $csv = "Waktu,User,Action,Entity,Entity ID,Perubahan\n";

        foreach ($logs as $log) {
            $changes = json_encode($log->changes ?? []);
            $csv .= sprintf(
                '"%s","%s","%s","%s","%s","%s"' . "\n",
                $log->created_at->format('Y-m-d H:i:s'),
                $log->user?->name ?? 'System',
                $log->action,
                $log->entity,
                $log->entity_id,
                str_replace('"', '""', $changes)
            );
        }

        return response()->streamDownload(
            fn() => print($csv),
            'audit-logs-' . now()->format('Y-m-d-His') . '.csv'
        );
    }

    public function render()
    {
        return view('livewire.admin.audit-log-viewer');
    }
}
