<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ImportCompletedNotification extends Notification
{
    use Queueable;

    protected int $successCount;
    protected int $errorCount;
    protected array $errors;

    public function __construct(int $successCount, int $errorCount, array $errors = [])
    {
        $this->successCount = $successCount;
        $this->errorCount = $errorCount;
        $this->errors = $errors;
    }

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $message = (new MailMessage)
            ->subject('Import SPPD Selesai')
            ->greeting('Halo ' . $notifiable->name . ',')
            ->line('Import data SPPD telah selesai.')
            ->line("Berhasil: {$this->successCount} data")
            ->line("Gagal: {$this->errorCount} data");

        if (!empty($this->errors)) {
            $message->line('Error detail:');
            foreach (array_slice($this->errors, 0, 5) as $error) {
                $message->line("- $error");
            }
        }

        return $message->action('Lihat Data SPPD', url('/spd'));
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'import_completed',
            'success_count' => $this->successCount,
            'error_count' => $this->errorCount,
            'errors' => array_slice($this->errors, 0, 10),
        ];
    }
}
