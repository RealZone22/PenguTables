<?php

namespace RealZone22\PenguTables\Tests\Traits;

use Illuminate\Support\Facades\Cache;
use PHPUnit\Framework\Attributes\Test;
use RealZone22\PenguTables\Table\Options;
use RealZone22\PenguTables\Tests\TestCase;
use RealZone22\PenguTables\Traits\WithCache;

class WithCacheTest extends TestCase
{
    #[Test]
    public function it_has_default_cache_duration()
    {
        $mock = $this->createCacheMock();

        $this->assertEquals(300, $mock->getCacheDurationPublic());
    }

    protected function createCacheMock()
    {
        return new class
        {
            use WithCache;

            public Options $options;

            public int $perPage = 10;

            public string $search = '';

            public string $sortField = '';

            public string $sortDirection = 'asc';

            public array $activeFilters = [];

            public function __construct()
            {
                $this->options = Options::make();
            }

            public function getPage(): int
            {
                return 1;
            }

            public function getCacheDurationPublic(): int
            {
                return $this->getCacheDuration();
            }

            public function getCacheKeyPrefixPublic(): string
            {
                return $this->getCacheKeyPrefix();
            }

            public function isCacheEnabledPublic(): bool
            {
                return $this->isCacheEnabled();
            }

            public function generateCacheKeyPublic(): string
            {
                return $this->generateCacheKey();
            }
        };
    }

    #[Test]
    public function it_can_set_custom_cache_duration_via_options()
    {
        $mock = $this->createCacheMock();
        $mock->options->cacheDuration = 600;

        $this->assertEquals(600, $mock->getCacheDurationPublic());
    }

    #[Test]
    public function it_generates_cache_key_prefix_from_class_name()
    {
        $mock = $this->createCacheMock();

        $prefix = $mock->getCacheKeyPrefixPublic();

        $this->assertStringStartsWith('pengutable_', $prefix);
    }

    #[Test]
    public function it_can_set_custom_cache_key_prefix_via_options()
    {
        $mock = $this->createCacheMock();
        $mock->options->cacheKeyPrefix = 'custom_prefix';

        $this->assertEquals('custom_prefix', $mock->getCacheKeyPrefixPublic());
    }

    #[Test]
    public function it_is_disabled_by_default()
    {
        $mock = $this->createCacheMock();

        $this->assertFalse($mock->isCacheEnabledPublic());
    }

    #[Test]
    public function it_can_be_enabled_via_options()
    {
        $mock = $this->createCacheMock();
        $mock->options->cacheEnabled = true;

        $this->assertTrue($mock->isCacheEnabledPublic());
    }

    #[Test]
    public function it_generates_unique_cache_keys_based_on_state()
    {
        $mock = $this->createCacheMock();

        $key1 = $mock->generateCacheKeyPublic();

        // Change state
        $mock->search = 'test';

        $key2 = $mock->generateCacheKeyPublic();

        $this->assertNotEquals($key1, $key2);
    }

    #[Test]
    public function it_can_clear_current_cache()
    {
        $mock = $this->createCacheMock();

        Cache::shouldReceive('forget')
            ->once()
            ->with($mock->generateCacheKeyPublic());

        $mock->clearCurrentCache();
    }

    #[Test]
    public function it_can_configure_cache_with_fluent_api()
    {
        $options = Options::make()
            ->withCache(true, 600, 'my_table');

        $this->assertTrue($options->cacheEnabled);
        $this->assertEquals(600, $options->cacheDuration);
        $this->assertEquals('my_table', $options->cacheKeyPrefix);
    }
}
