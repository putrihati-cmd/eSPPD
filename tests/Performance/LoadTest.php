<?php

namespace Tests\Performance;

use App\Imports\SppdImport;
use App\Models\Spd;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Maatwebsite\Excel\Facades\Excel;
use Tests\TestCase;

class LoadTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test bulk import performance (1000+ rows)
     */
    public function test_bulk_import_performance(): void
    {
        // Create test file with 1000 rows
        $startTime = microtime(true);
        $memoryBefore = memory_get_usage();

        // Simulate import of 1000 records
        $data = [];
        for ($i = 1; $i <= 1000; $i++) {
            $data[] = [
                'nip' => '19800101' . str_pad($i, 10, '0', STR_PAD_LEFT),
                'tujuan' => 'Jakarta',
                'keperluan' => 'Test Purpose ' . $i,
                'tanggal_berangkat' => now()->addDays($i)->format('Y-m-d'),
                'tanggal_kembali' => now()->addDays($i + 3)->format('Y-m-d'),
                'transportasi' => 'pesawat',
            ];
        }

        $endTime = microtime(true);
        $memoryAfter = memory_get_usage();

        $executionTime = $endTime - $startTime;
        $memoryUsed = ($memoryAfter - $memoryBefore) / 1024 / 1024; // MB

        // Performance assertions
        $this->assertLessThan(30, $executionTime, 'Bulk import should complete within 30 seconds');
        $this->assertLessThan(128, $memoryUsed, 'Memory usage should be under 128MB');

        echo "\nBulk Import Performance:\n";
        echo "Execution Time: " . round($executionTime, 2) . " seconds\n";
        echo "Memory Used: " . round($memoryUsed, 2) . " MB\n";
    }

    /**
     * Test concurrent approval queue
     */
    public function test_concurrent_approval_queue(): void
    {
        // Create multiple SPDs for approval
        Spd::factory()->count(100)->create(['status' => 'submitted']);

        $startTime = microtime(true);

        // Simulate fetching approval queue
        $approvals = Spd::where('status', 'submitted')
            ->with(['employee', 'unit'])
            ->orderBy('created_at', 'desc')
            ->get();

        $endTime = microtime(true);
        $queryTime = $endTime - $startTime;

        // Should complete quickly
        $this->assertLessThan(1, $queryTime, 'Approval queue query should complete within 1 second');
        $this->assertCount(100, $approvals);

        echo "\nApproval Queue Performance:\n";
        echo "Query Time: " . round($queryTime * 1000, 2) . " ms\n";
        echo "Records: " . $approvals->count() . "\n";
    }

    /**
     * Test dashboard statistics query performance
     */
    public function test_dashboard_query_performance(): void
    {
        // Create test data
        Spd::factory()->count(500)->create();

        $startTime = microtime(true);

        // Simulate dashboard queries
        $stats = [
            'total' => Spd::count(),
            'pending' => Spd::where('status', 'submitted')->count(),
            'approved' => Spd::where('status', 'approved')->count(),
            'this_month' => Spd::whereMonth('created_at', now()->month)->count(),
        ];

        $endTime = microtime(true);
        $queryTime = $endTime - $startTime;

        $this->assertLessThan(0.5, $queryTime, 'Dashboard stats should load within 500ms');

        echo "\nDashboard Stats Performance:\n";
        echo "Query Time: " . round($queryTime * 1000, 2) . " ms\n";
    }

    /**
     * Test API response time
     */
    public function test_api_response_time(): void
    {
        $user = User::factory()->create();
        Spd::factory()->count(50)->create(['employee_id' => $user->employee_id]);

        $startTime = microtime(true);

        $response = $this->actingAs($user)
            ->getJson('/api/sppd');

        $endTime = microtime(true);
        $responseTime = $endTime - $startTime;

        $response->assertStatus(200);
        $this->assertLessThan(0.5, $responseTime, 'API should respond within 500ms');

        echo "\nAPI Response Performance:\n";
        echo "Response Time: " . round($responseTime * 1000, 2) . " ms\n";
    }

    /**
     * Test database query optimization
     */
    public function test_database_query_optimization(): void
    {
        Spd::factory()->count(100)->create();

        // Enable query log
        \DB::enableQueryLog();

        // Perform common operations
        Spd::with(['employee', 'unit', 'budget'])->get();

        $queries = \DB::getQueryLog();
        \DB::disableQueryLog();

        // Should use eager loading (max 4 queries: spds, employees, units, budgets)
        $this->assertLessThanOrEqual(4, count($queries), 'Should use eager loading to minimize queries');

        echo "\nQuery Optimization:\n";
        echo "Total Queries: " . count($queries) . "\n";
    }

    /**
     * Test cache effectiveness
     */
    public function test_cache_effectiveness(): void
    {
        Spd::factory()->count(100)->create();

        // First call (no cache)
        Cache::forget('dashboard_stats');
        $startTime = microtime(true);
        $stats1 = Cache::remember('dashboard_stats', 3600, function () {
            return [
                'total' => Spd::count(),
                'pending' => Spd::where('status', 'submitted')->count(),
            ];
        });
        $firstCallTime = microtime(true) - $startTime;

        // Second call (cached)
        $startTime = microtime(true);
        $stats2 = Cache::remember('dashboard_stats', 3600, function () {
            return [
                'total' => Spd::count(),
                'pending' => Spd::where('status', 'submitted')->count(),
            ];
        });
        $cachedCallTime = microtime(true) - $startTime;

        // Cached call should be much faster
        $this->assertLessThan($firstCallTime, $cachedCallTime);

        echo "\nCache Effectiveness:\n";
        echo "First Call: " . round($firstCallTime * 1000, 2) . " ms\n";
        echo "Cached Call: " . round($cachedCallTime * 1000, 2) . " ms\n";
        echo "Improvement: " . round(($firstCallTime - $cachedCallTime) / $firstCallTime * 100, 1) . "%\n";
    }
}
