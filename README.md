## About laravel-table-cache (Work in progress)

If you are working with a lot of data, it may not be best practice to work with collections (who are cached by laravel).

Instead of using (which returns a collection):
```php
// for example in a cronjob that caches the users table
$users = cache()->rememberForever('users', function () {
    return DB::table('users')->pluck('email', 'id');
});
// when you need to check if this user exists somewhere later in your application
$users->where('email', 'john@do.nl')->exists();
```

We could do (which caches all the key values from the model directly in the cache):

```php
// for example in a cronjob that caches the users table
User::cacheColumnKey('email')->withColumnValue('id');
// when you need to check if this user exists somewhere later in your application
User::cacheColumnKey('email')->isCached('john@do.nl');
// or if you need the value
User::cacheColumnKey('email')->getKeyValue('john@do.nl'); // returns the id in this case
```

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
        
        BlackList::cacheColumnKey('name')->withColumnValue('your_column_name');
    }
```

You can also use a default value:

```php
    public function run()
    {
        // Your seed code here...
        
        BlackList::cacheColumnKey('name')->withDefaultValue(true);
    }
```

To check if the value has been cached

```php
BlackList::cacheColumnKey('name')->isCached('suspicious.com')
```

To get the cached value

```php
BlackList::cacheColumnKey('name')->getKeyValue('suspicious.com')
```

To check if the table column is already cached:

```php
BlackList::hasTableKeyCached('name')
```
