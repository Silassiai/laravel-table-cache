## About laravel-table-cache

Solution to easily cache tables records, the package uses the laravel Cache facade.

- Cache 2 table columns as key value pairs [Trait Key Value](#trait-key-value)

## Installation

You can install the package via composer:

```bash
composer require silassiai/laravel-table-cache
```

## Basic usage

### Trait Key Value

You can add the `TableCacheKeyValueTrait` to you model to easily cache key value pairs (two columns) for your whole table.
This can be handy when you want to cache the whole table after you seeded the model.

```php
<?php

namespace App\Models;

use Silassiai\LaravelTableCache\Traits\TableCacheKeyValueTrait;

class BlackList
{
    use TableCacheKeyValueTrait;
}
```

next add to your seeder:

```php
    public function run()
    {
        // Your seed code here...
        
        BlackList::forColumnKey(BlackList::NAME)->withColumnValue('your_column_name');
    }
```

You can also use a default value:

```php
    public function run()
    {
        // Your seed code here...
        
        BlackList::forColumnKey(BlackList::NAME)->withValue(true);
    }
```