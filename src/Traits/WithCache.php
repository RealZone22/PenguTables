<?php

namespace RealZone22\PenguTables\Traits;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Cache;
use RealZone22\PenguTables\Table\Options;

/**
 * Trait for caching PenguTable data.
 *
 * @property int $perPage
 * @property string $search
 * @property string $sortField
 * @property string $sortDirection
 * @property array $activeFilters
 * @property Options $options
 *
 * @method int getPage()
 * @method Builder query()
 * @method Builder applySearch(Builder $query)
 * @method Builder applyFilters(Builder $query)
 * @method Builder applySort(Builder $query)
 */
trait WithCache
{
    /**
     * Override the getDataProperty method to add caching.
     */
    public function getDataProperty(): LengthAwarePaginator
    {
        if (!$this->isCacheEnabled()) {
            return $this->fetchData();
        }

        $cacheKey = $this->generateCacheKey();

        return Cache::remember($cacheKey, $this->getCacheDuration(), function () {
            return $this->fetchData();
        });
    }

    /**
     * Check if caching is enabled from options.
     */
    protected function isCacheEnabled(): bool
    {
        return $this->options->cacheEnabled;
    }

    /**
     * Fetch data from the database without caching.
     */
    protected function fetchData(): LengthAwarePaginator
    {
        $query = $this->query();
        $query = $this->applySearch($query);
        $query = $this->applyFilters($query);
        $query = $this->applySort($query);

        return $query->paginate($this->perPage);
    }

    /**
     * Generate a unique cache key based on current table state.
     */
    protected function generateCacheKey(): string
    {
        $prefix = $this->getCacheKeyPrefix();

        $keyComponents = [
            'page' => $this->getPage(),
            'perPage' => $this->perPage,
            'search' => $this->search,
            'sortField' => $this->sortField,
            'sortDirection' => $this->sortDirection,
            'filters' => json_encode($this->activeFilters ?? []),
        ];

        $hash = md5(serialize($keyComponents));

        return "{$prefix}_{$hash}";
    }

    /**
     * Get the cache key prefix from options or generate from class name.
     */
    protected function getCacheKeyPrefix(): string
    {
        if ($this->options->cacheKeyPrefix) {
            return $this->options->cacheKeyPrefix;
        }

        return 'pengutable_' . strtolower(class_basename(static::class));
    }

    /**
     * Get the cache duration in seconds from options.
     */
    protected function getCacheDuration(): int
    {
        return $this->options->cacheDuration;
    }

    /**
     * Clear all cache for this table (requires cache driver that supports tags).
     */
    public function clearTableCache(): void
    {
        $prefix = $this->getCacheKeyPrefix();
        Cache::tags($prefix)->flush();
    }

    /**
     * Refresh the current page data by clearing cache and re-fetching.
     */
    public function refreshCache(): void
    {
        $this->clearCurrentCache();
    }

    /**
     * Clear cache for the current state.
     */
    public function clearCurrentCache(): void
    {
        $cacheKey = $this->generateCacheKey();
        Cache::forget($cacheKey);
    }
}
