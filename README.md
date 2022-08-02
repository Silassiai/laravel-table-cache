## About laravel-table-cache

Solution to easily cache tables records, the package uses the laravel Cache facade.

- Cache 2 table columns as key value pairs [Trait Key Value](#-trait-key-value)

## Basic usage

### Trait Key Value

You can add the `TableCacheKeyValueTrait` to you model to easily cache key value pairs (two columns) for your whole table.
This can be handy when you want to cache the whole table after you seeded the model.

```php
<?php

namespace App\Models;

use Silassiai\LaravelTableCache\Traits\TableCacheKeyValueTrait;

class class User
{
    use TableCacheKeyValueTrait;
}
```