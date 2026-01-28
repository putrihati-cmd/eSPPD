<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Route;
use App\Models\User;
use App\Models\Employee;
use App\Models\Spd;

class ValidateSystem extends Command
{
    // ...
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'system:validate';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Perform comprehensive system validation (DB, API, Services)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting System Validation...');

        // 1. Database Integrity Check
        $this->checkDatabase();

        // 2. API Endpoint Validation
        $this->checkApiEndpoints();

        // 3. Python Service Check
        $this->checkPythonService();

        // 4. Data Consistency
        $this->checkDataConsistency();

        $this->info('Validation Complete.');
    }

    private function checkDatabase()
    {
        $this->warn('1. Checking Database Connection...');
        try {
            DB::connection()->getPdo();
            $this->info('   [PASS] Database Connected: ' . DB::connection()->getDatabaseName());
        } catch (\Exception $e) {
            $this->error('   [FAIL] Database Connection Failed: ' . $e->getMessage());
            return;
        }

        // Check Table Existence (Critical Tables)
        $tables = ['users', 'employees', 'spds', 'trip_reports'];
        foreach ($tables as $table) {
            if (DB::getSchemaBuilder()->hasTable($table)) {
                $this->info("   [PASS] Table '$table' exists.");
            } else {
                $this->error("   [FAIL] Table '$table' MISSING.");
            }
        }
    }

    private function checkApiEndpoints()
    {
        $this->warn('2. Checking Critical Routes...');
        $routes = [
            'login' => ['GET'],
            'dashboard' => ['GET'],
            'spd.index' => ['GET'],
            'spd.create' => ['GET'],
        ];

        foreach ($routes as $name => $methods) {
            if (Route::has($name)) {
                $this->info("   [PASS] Route '$name' exists.");
            } else {
                $this->error("   [FAIL] Route '$name' NOT FOUND.");
            }
        }
    }

    private function checkPythonService()
    {
        $this->warn('3. Checking Python Microservice...');
        $url = config('services.python_document.url', 'http://localhost:8001');
        
        try {
            $response = Http::timeout(2)->get("$url/health");
            if ($response->successful()) {
                $data = $response->json();
                $this->info("   [PASS] Service Healthy. Status: " . ($data['status'] ?? 'Unknown'));
            } else {
                $this->error("   [FAIL] Service Error: " . $response->status());
            }
        } catch (\Exception $e) {
            $this->error("   [FAIL] Service Unreachable at $url. Is Docker running?");
        }
    }

    private function checkDataConsistency()
    {
        $this->warn('4. Checking Data Consistency...');

        try {
            // Orphaned SPPDs (No Employee)
            $orphanedSppd = Spd::whereDoesntHave('employee')->count();
            if ($orphanedSppd > 0) {
                $this->error("   [WARN] Found $orphanedSppd SPPDs without valid Employee.");
            } else {
                $this->info("   [PASS] All SPPDs linked to Employees.");
            }

            // Employee - User Link
            $usersWithoutEmployee = User::whereDoesntHave('employee')->count();
            if ($usersWithoutEmployee > 0) {
                $this->comment("   [INFO] Found $usersWithoutEmployee Users without Employee data.");
            }
        } catch (\Exception $e) {
            $this->error("   [FAIL] Data Consistency Check Failed: " . $e->getMessage());
        }
    }
}
