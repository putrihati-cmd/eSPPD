<?php

namespace App\Listeners;

use Symfony\Component\Console\Event\ConsoleCommandEvent;
use Illuminate\Support\Facades\Log;

class SyncDatabaseAfterArtisan
{
    /**
     * List of artisan commands that trigger database sync
     */
    private $triggerCommands = [
        'migrate',
        'migrate:rollback',
        'migrate:refresh',
        'db:seed',
        'tinker',
        'make:migration',
        'make:model',
        'make:seeder',
    ];

    public function handle(ConsoleCommandEvent $event)
    {
        $command = $event->getCommand();

        if (!$command) {
            return;
        }

        $commandName = $command->getName();

        // Check if this command should trigger sync
        $shouldSync = false;
        foreach ($this->triggerCommands as $trigger) {
            if (strpos($commandName, $trigger) !== false) {
                $shouldSync = true;
                break;
            }
        }

        if (!$shouldSync) {
            return;
        }

        // Register callback to run after command completes
        $event->getCommand()->getApplication()->setCode(function ($input, $output) use ($event) {
            // This will run the actual command first
            $statusCode = $event->getCommand()->run($input, $output);

            // Then sync to production
            if ($statusCode === 0 && env('AUTO_DB_SYNC') === true) {
                $output->writeln("\n<info>🔄 Auto-syncing database to production...</info>");

                $app = app();
                $app['artisan']->call('db:sync-to-production', [], $output);
            }

            return $statusCode;
        });
    }
}
