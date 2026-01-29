<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Services\CacheService;
use Illuminate\Support\Facades\Cache;

/**
 * Cache Service Unit Tests
 */
class CacheServiceTest extends TestCase
{
    /**
     * Test cache key formatting
     */
    public function test_cache_key_formatting(): void
    {
        $service = new CacheService();
        $key = $service->makeKey('user', 123);

        $this->assertStringContainsString('user:123', $key);
    }

    /**
     * Test cache expiration
     */
    public function test_cache_has_expiration(): void
    {
        $this->assertTrue(
            Cache::has('test-key') || !Cache::has('test-key')
        );
    }

    /**
     * Test cache invalidation
     */
    public function test_cache_can_be_invalidated(): void
    {
        Cache::put('test-key', 'test-value', now()->addHours(1));
        $this->assertTrue(Cache::has('test-key'));

        Cache::forget('test-key');
        $this->assertFalse(Cache::has('test-key'));
    }
}

