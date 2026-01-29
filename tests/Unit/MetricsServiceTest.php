<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Services\MetricsService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;

/**
 * Metrics Service Unit Tests
 */
class MetricsServiceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        MetricsService::reset();
    }

    /**
     * Test request metric recording
     */
    public function test_record_request_increments_counters(): void
    {
        MetricsService::recordRequest('GET', '/api/spd', 200, 150);

        $snapshot = MetricsService::getSnapshot();

        $this->assertEquals(1, $snapshot['requests']['total']);
        $this->assertEquals(1, $snapshot['requests']['by_method']['GET']);
    }

    /**
     * Test error rate tracking
     */
    public function test_error_status_codes_are_tracked(): void
    {
        MetricsService::recordRequest('GET', '/api/spd', 500, 100);
        MetricsService::recordRequest('POST', '/api/spd', 200, 100);

        $snapshot = MetricsService::getSnapshot();

        $this->assertGreaterThan(0, $snapshot['requests']['errors']);
    }

    /**
     * Test cache metrics
     */
    public function test_cache_operation_recording(): void
    {
        MetricsService::recordCacheOperation('set', 'user:123', false);
        MetricsService::recordCacheOperation('get', 'user:123', true);

        $snapshot = MetricsService::getSnapshot();

        $this->assertEquals(1, $snapshot['cache']['hits']);
        $this->assertEquals(1, $snapshot['cache']['misses']);
    }

    /**
     * Test queue metrics
     */
    public function test_queue_job_recording(): void
    {
        MetricsService::recordQueueJob('default', 'GeneratePdfJob', 500, true);
        MetricsService::recordQueueJob('default', 'SendEmailJob', 1000, false);

        $snapshot = MetricsService::getSnapshot();

        $this->assertGreaterThan(0, $snapshot['queue']['by_queue']['default']);
    }

    /**
     * Test business event recording
     */
    public function test_business_event_recording(): void
    {
        MetricsService::recordBusinessEvent('sppd_created', 'sppd');
        MetricsService::recordBusinessEvent('sppd_approved', 'sppd');
        MetricsService::recordBusinessEvent('sppd_rejected', 'sppd');

        $snapshot = MetricsService::getSnapshot();

        $this->assertEquals(3, $snapshot['business']['total_events']);
    }
}
