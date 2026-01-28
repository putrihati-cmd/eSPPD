<?php

namespace App\Notifications;

use App\Models\Spd;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SppdApprovalNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected Spd $spd;
    protected string $action;

    public function __construct(Spd $spd, string $action = 'pending')
    {
        $this->spd = $spd;
        $this->action = $action;
    }

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $subject = match($this->action) {
            'pending' => 'SPPD Menunggu Persetujuan Anda',
            'approved' => 'SPPD Anda Telah Disetujui',
            'rejected' => 'SPPD Anda Ditolak',
            'reminder' => 'Reminder: SPPD Menunggu Persetujuan',
            'escalated' => 'SPPD Dialihkan ke Anda',
            default => 'Update Status SPPD',
        };

        $message = (new MailMessage)
            ->subject($subject)
            ->greeting('Halo ' . $notifiable->name . ',')
            ->line('Nomor SPPD: ' . $this->spd->spd_number)
            ->line('Pegawai: ' . $this->spd->employee->name)
            ->line('Tujuan: ' . $this->spd->destination)
            ->line('Tanggal: ' . $this->spd->departure_date->format('d/m/Y') . ' - ' . $this->spd->return_date->format('d/m/Y'));

        if ($this->action === 'pending' || $this->action === 'reminder' || $this->action === 'escalated') {
            $message->action('Lihat Detail SPPD', url('/spd/' . $this->spd->id));
        }

        return $message->line('Terima kasih menggunakan e-SPPD.');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'spd_id' => $this->spd->id,
            'spd_number' => $this->spd->spd_number,
            'action' => $this->action,
            'employee_name' => $this->spd->employee->name,
            'destination' => $this->spd->destination,
        ];
    }
}
