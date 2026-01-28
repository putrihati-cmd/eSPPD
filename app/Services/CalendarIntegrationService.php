<?php

namespace App\Services;

use App\Models\Spd;
use Google_Client;
use Google_Service_Calendar;
use Google_Service_Calendar_Event;
use Illuminate\Support\Facades\Log;

class CalendarIntegrationService
{
    protected ?Google_Service_Calendar $service = null;

    public function __construct()
    {
        if (config('services.google.calendar_enabled')) {
            $this->initializeGoogleClient();
        }
    }

    protected function initializeGoogleClient(): void
    {
        try {
            $client = new Google_Client();
            $client->setAuthConfig(storage_path('app/google-credentials.json'));
            $client->addScope(Google_Service_Calendar::CALENDAR);
            $client->setAccessType('offline');

            $this->service = new Google_Service_Calendar($client);
        } catch (\Exception $e) {
            Log::error('Google Calendar initialization failed', ['error' => $e->getMessage()]);
        }
    }

    /**
     * Sync SPPD to Google Calendar
     */
    public function syncToCalendar(Spd $spd, string $calendarId = 'primary'): ?string
    {
        if (!$this->service) {
            Log::warning('Google Calendar service not available');
            return null;
        }

        try {
            $event = new Google_Service_Calendar_Event([
                'summary' => "SPPD: {$spd->destination}",
                'description' => implode("\n", [
                    "Nomor SPPD: {$spd->spd_number}",
                    "Pegawai: {$spd->employee?->name}",
                    "Keperluan: {$spd->purpose}",
                    "Transportasi: {$spd->transport_type}",
                ]),
                'start' => [
                    'date' => $spd->departure_date->format('Y-m-d'),
                    'timeZone' => 'Asia/Jakarta',
                ],
                'end' => [
                    'date' => $spd->return_date->addDay()->format('Y-m-d'),
                    'timeZone' => 'Asia/Jakarta',
                ],
                'reminders' => [
                    'useDefault' => false,
                    'overrides' => [
                        ['method' => 'email', 'minutes' => 24 * 60], // 1 day before
                        ['method' => 'popup', 'minutes' => 60], // 1 hour before
                    ],
                ],
            ]);

            $createdEvent = $this->service->events->insert($calendarId, $event);

            // Store event ID in SPD for future updates
            $spd->update(['calendar_event_id' => $createdEvent->getId()]);

            Log::info('Google Calendar event created', [
                'spd_id' => $spd->id,
                'event_id' => $createdEvent->getId(),
            ]);

            return $createdEvent->getId();

        } catch (\Exception $e) {
            Log::error('Failed to sync to Google Calendar', [
                'spd_id' => $spd->id,
                'error' => $e->getMessage(),
            ]);
            return null;
        }
    }

    /**
     * Update calendar event when SPPD changes
     */
    public function updateCalendarEvent(Spd $spd): bool
    {
        if (!$this->service || !$spd->calendar_event_id) {
            return false;
        }

        try {
            $event = $this->service->events->get('primary', $spd->calendar_event_id);
            
            $event->setSummary("SPPD: {$spd->destination}");
            $event->setDescription(implode("\n", [
                "Nomor SPPD: {$spd->spd_number}",
                "Pegawai: {$spd->employee?->name}",
                "Keperluan: {$spd->purpose}",
                "Status: {$spd->status_label}",
            ]));

            $this->service->events->update('primary', $spd->calendar_event_id, $event);

            return true;

        } catch (\Exception $e) {
            Log::error('Failed to update calendar event', ['error' => $e->getMessage()]);
            return false;
        }
    }

    /**
     * Delete calendar event
     */
    public function deleteCalendarEvent(Spd $spd): bool
    {
        if (!$this->service || !$spd->calendar_event_id) {
            return false;
        }

        try {
            $this->service->events->delete('primary', $spd->calendar_event_id);
            $spd->update(['calendar_event_id' => null]);
            return true;

        } catch (\Exception $e) {
            Log::error('Failed to delete calendar event', ['error' => $e->getMessage()]);
            return false;
        }
    }
}
