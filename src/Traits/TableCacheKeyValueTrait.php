<?php

declare(strict_types=1);

namespace Silassiai\LaravelTableCache\Traits;

use App\Models\BlackList;
use Illuminate\Support\Facades\Cache;
use Silassiai\LaravelTableCache\Exceptions\ColumnNotFoundException;
use Throwable;

trait TableCacheKeyValueTrait
{
    /** @var string $cacheColumnKey */
    private ?string $cacheColumnKey = null;
    /** @var string|null $cacheColumnValue */
    private ?string $cacheColumnValue = null;
    /** @var mixed $cacheValue */
    private mixed $cacheValue;
    /** @var bool $useCacheColumnValue */
    private bool $useCacheColumnValue = true;

    /**
     * @param string $cacheColumnKey
     * @return TableCacheKeyValueTrait|BlackList
     */
    public static function cacheColumnKey(string $cacheColumnKey): self
    {
        $model = app(static::class);
        $model->cacheColumnKey = $cacheColumnKey;
        return $model;
    }

    /**
     * @param string $cacheColumnValue
     * @return TableCacheKeyValueTrait|BlackList
     * @throws Throwable
     */
    public function withColumnValue(string $cacheColumnValue): self
    {
        $this->cacheColumnValue = $cacheColumnValue;
        $this->cacheKeyValues();

        return $this;
    }

    /**
     * @param $cacheValue
     * @return TableCacheKeyValueTrait|BlackList
     * @throws Throwable
     */
    public function withDefaultValue($cacheValue): self
    {
        $this->cacheValue = $cacheValue;
        $this->notUseCacheColumnValue();
        $this->cacheKeyValues();

        return $this;
    }

    /**
     * @throws Throwable
     */
    public function cacheKeyValues(): void
    {
        $model = $this;
        $table = self::getTableName();
        $columns = $model->getConnection()->getSchemaBuilder()->getColumnListing($table);

        $cacheColumnKey = $this->cacheColumnKey;
        $cacheColumnValue = $this->cacheColumnValue;
        $cacheValue = $this->cacheValue;
        $useCacheColumnValue = $this->useCacheColumnValue;

        throw_if(
            !in_array($cacheColumnKey, $columns, true),
            ColumnNotFoundException::class, ...[$cacheColumnKey, $table]
        );

        throw_if(
            $useCacheColumnValue && '' !== $cacheColumnValue && !in_array($cacheColumnValue, $columns, true),
            ColumnNotFoundException::class, ...[$cacheColumnValue, $table]
        );

        static::class::chunk(500, static function ($records) use ($table, $cacheColumnKey, $cacheColumnValue, $cacheValue, $useCacheColumnValue, $model) {
            foreach ($records as $record) {
                Cache::forever(
                    $table . ':' . $model->cacheColumnKey . ':' . $record->{$cacheColumnKey},
                    $useCacheColumnValue ? $record->{$cacheColumnValue} : $cacheValue
                );
            }
        });
        Cache::forever('silassiai:' . $table . ':' . $model->cacheColumnKey . ':cached', true);
    }

    /**
     * @return void
     */
    public function notUseCacheColumnValue(): void
    {
        $this->useCacheColumnValue = false;
    }

    /**
     * To see if the table has been cached with this package
     * @param $key
     * @return bool
     */
    public static function hasTableKeyCached($key): bool
    {
        return Cache::has('silassiai:' . self::getTableName() . ':' . $key . ':cached');
    }

    /**
     * @return string
     */
    public static function getTableName(): string
    {
        return app(static::class)->getTable();
    }

    /**
     * @param string $cacheKey
     * @return bool
     */
    public function isCached(string $cacheKey): bool
    {
        return Cache::has(self::getTableName().':'. $this->cacheColumnKey . ':' .$cacheKey);
    }

    /**
     * @param string $cacheKey
     * @return bool
     */
    public function getKeyValue(string $cacheKey): bool
    {
        return Cache::get(self::getTableName().':'. $this->cacheColumnKey . ':' .$cacheKey);
    }
}