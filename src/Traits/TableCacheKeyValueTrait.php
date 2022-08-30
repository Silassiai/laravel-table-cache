<?php

declare(strict_types=1);

namespace Silassiai\LaravelTableCache\Traits;

use App\Models\BlackList;
use Illuminate\Support\Facades\Cache;
use Silassiai\LaravelTableCache\Exceptions\ColumnNotFoundException;

trait TableCacheKeyValueTrait
{
    private string $cacheColumnKey;

    private ?string $cacheColumnValue = null;
    private $cacheValue;

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
     * @return self
     */
    public function withColumnValue(string $cacheColumnValue): self
    {
        $this->cacheColumnValue = $cacheColumnValue;

        $this->cacheKeyValues();

        return $this;
    }

    /**
     * @param $cacheValue
     * @return self
     */
    public function withDefaultValue($cacheValue): self
    {
        $this->cacheValue = $cacheValue;

        $this->notUseCacheColumnValue();

        $this->cacheKeyValues();

        return $this;
    }

    public function cacheKeyValues(): void
    {
        $model = app(static::class);
        $table = $model->getTable();
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

        static::class::chunk(500, static function ($records)
        use ($table, $cacheColumnKey, $cacheColumnValue, $cacheValue, $useCacheColumnValue) {
            foreach ($records as $record) {
                Cache::forever(
                    $table . ':' . $record->{$cacheColumnKey},
                    $useCacheColumnValue ? $record->{$cacheColumnValue} : $cacheValue
                );
            }
        });
        Cache::forever('silassiai:' . $table . ':cached', true);
    }

    public function notUseCacheColumnValue(): void
    {
        $this->useCacheColumnValue = false;
    }

    /**
     * To see if the table has been cached with this package
     * @return bool
     */
    public static function hasTableCached(): bool
    {
        $model = app(static::class);
        $table = $model->getTable();
        return Cache::has('silassiai:' . $table . ':cached');
    }
}