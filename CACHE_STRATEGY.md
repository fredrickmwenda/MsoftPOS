# POS Cache Strategy Documentation

## Overview

This document describes the caching strategy implemented in the JoexPOS system to optimize performance, particularly for the Sales (POS) module. The system uses Laravel's Cache facade with automatic invalidation through model observers.

## Cache Architecture

### Cache Layers

The caching system is organized into three layers based on data update frequency:

1. **Frequent Data (24-hour cache)**
   - Customer list
   - Customer groups
   - Product list (standard)
   - Product list (with variants)
   - These items change frequently and need shorter cache duration

2. **Reference Data (30-day cache)**
   - Warehouse list
   - Biller list
   - Tax list
   - Brand list
   - Category list
   - Table list
   - Coupon list
   - POS settings
   - These items change infrequently but need to be available

3. **Structural Data (365-day cache)**
   - Warehouse structural information
   - These items rarely change and can be cached long-term

## Implementation Details

### Cache Keys

All cache keys follow this naming convention:
- `{entity}_list` for list queries (e.g., `customer_list`, `product_list`)
- `user_permissions_{role_id}` for permission data

### Cache Storage

Cache is stored using Laravel's default cache driver (configured in `.env`). Options include:
- `file` - File-based cache (default for development)
- `redis` - Redis cache (recommended for production)
- `memcached` - Memcached (alternative for production)

## Automatic Invalidation System

### Model Observers

Each cached entity has a corresponding observer that automatically invalidates its cache when the model is created, updated, deleted, or restored.

#### Observers Implemented

| Model | Observer | Cache Key(s) |
|-------|----------|--------------|
| `Product` | `ProductObserver` | `product_list`, `product_list_with_variant` |
| `Customer` | `CustomerObserver` | `customer_list` |
| `CustomerGroup` | `CustomerGroupObserver` | `customer_group_list` |
| `Warehouse` | `WarehouseObserver` | `warehouse_list` |
| `Biller` | `BillerObserver` | `biller_list` |
| `Tax` | `TaxObserver` | `tax_list` |
| `Brand` | `BrandObserver` | `brand_list` |
| `Category` | `CategoryObserver` | `category_list` |
| `Table` | `TableObserver` | `table_list` |
| `Coupon` | `CouponObserver` | `coupon_list` |
| `PosSetting` | `PosSettingObserver` | `pos_setting` |
| `Roles` | `RoleObserver` | `user_permissions_{role_id}` |

### Observer Registration

All observers are registered in `app/Providers/AppServiceProvider.php`:

```php
public function boot()
{
    Product::observe(ProductObserver::class);
    Customer::observe(CustomerObserver::class);
    // ... other observers
}
```

## SaleController Caching Methods

### `getUserPermissions()`

Retrieves and caches user permissions for the current role.

```php
private function getUserPermissions()
{
    $cacheKey = 'user_permissions_' . Auth::user()->role_id;
    
    return Cache::remember($cacheKey, 60*60*24, function () {
        $role = Role::find(Auth::user()->role_id);
        $permissions = $role->permissions->pluck('name')->toArray();
        return array_combine($permissions, array_fill(0, count($permissions), true));
    });
}
```

**Returns**: Associative array where keys are permission names and values are `true`

**Usage in Blade**: `$permissions['permission-name']` for O(1) lookup

### `getPosDashboardData()`

Retrieves all dashboard data with appropriate caching for the POS module.

```php
private function getPosDashboardData()
{
    // Returns array of cached data for dashboard
    return [
        'lims_customer_list' => $lims_customer_list,
        'lims_product_list' => $lims_product_list,
        // ... other data
    ];
}
```

### Cache Invalidation Methods

Located in `SaleController`:

- `invalidatePermissionCache($roleId)` - Clear permission cache for a role
- `invalidateCustomerCache()` - Clear customer and customer group caches
- `invalidateProductCache()` - Clear product list caches
- `invalidateInventoryCache()` - Clear warehouse cache
- `invalidateReferenceDataCache()` - Clear all reference data caches
- `invalidateAllSalesCache()` - Clear all sales-related caches

**Manual Usage** (if needed outside observers):

```php
app(SaleController::class)->invalidateCustomerCache();
app(SaleController::class)->invalidateAllSalesCache();
```

## Performance Impact

### Before Caching
- **Queries per POS page load**: 15-20+
- **Database load**: High on repeated page loads
- **Response time**: Variable depending on database load

### After Caching
- **Queries per POS page load**: 2-3
- **Database load**: Minimal (initial load + observer updates)
- **Response time**: Consistent and fast (cache hits)

## Adding New Cached Data

### Step 1: Add cache key to `getPosDashboardData()`

```php
$my_new_list = Cache::remember('my_new_list', 60*60*24, function () {
    return MyModel::where('is_active', true)->get();
});
```

### Step 2: Create observer class

Create `app/Observers/MyModelObserver.php`:

```php
<?php

namespace App\Observers;

use App\Models\MyModel;
use Illuminate\Support\Facades\Cache;

class MyModelObserver
{
    public function created(MyModel $model): void
    {
        Cache::forget('my_new_list');
    }

    public function updated(MyModel $model): void
    {
        Cache::forget('my_new_list');
    }

    public function deleted(MyModel $model): void
    {
        Cache::forget('my_new_list');
    }

    public function restored(MyModel $model): void
    {
        Cache::forget('my_new_list');
    }

    public function forceDeleted(MyModel $model): void
    {
        Cache::forget('my_new_list');
    }
}
```

### Step 3: Register observer in AppServiceProvider

```php
use App\Models\MyModel;
use App\Observers\MyModelObserver;

public function boot()
{
    MyModel::observe(MyModelObserver::class);
}
```

### Step 4: Add invalidation method to SaleController (optional)

```php
public function invalidateMyNewCache()
{
    Cache::forget('my_new_list');
}
```

## Cache Debugging

### Clear All Cache

```bash
php artisan cache:clear
```

### Clear Specific Cache Key

```bash
php artisan tinker
Cache::forget('product_list');
Cache::forget('customer_list');
```

### Check Cache Hit/Miss

Add logging to observer or cache retrieval:

```php
if (Cache::has('product_list')) {
    Log::info('Cache HIT: product_list');
} else {
    Log::info('Cache MISS: product_list');
}
```

## Troubleshooting

### Problem: Stale Data After Updates

**Cause**: Observer not firing or cache not being forgotten

**Solution**: 
1. Verify observer is registered in `AppServiceProvider::boot()`
2. Check that model uses `SoftDeletes` if applicable
3. Verify cache driver is working (`php artisan cache:clear`)

### Problem: Permission Changes Not Reflected

**Cause**: Permission cache not invalidated when role/permission updated

**Solution**: 
1. Check `RoleObserver` is registered
2. Clear cache manually: `Cache::forget('user_permissions_' . $roleId)`
3. Check that role changes trigger `updated` event

### Problem: High Database Load Despite Caching

**Cause**: Cache driver not working or observers creating new queries

**Solution**:
1. Verify cache driver configuration in `.env`
2. Check for N+1 queries in observers
3. Use `eager loading` in cache retrieval functions

## Testing Cache Effectiveness

### Manual Testing

1. **First Load** (cache miss):
   - Monitor database queries
   - Note response time

2. **Second Load** (cache hit):
   - Should see 0 queries for cached data
   - Response time should be significantly faster

3. **After Data Update**:
   - Cache should be invalidated by observer
   - Next load should see fresh data from database

### Query Logging

Enable query logging to verify caching:

```php
// In .env
DB_QUERY_LOG=true

// In code
DB::enableQueryLog();
// ... your code ...
dd(DB::getQueryLog());
```

## Best Practices

1. **Choose appropriate cache TTLs**
   - Frequently changing data: 24 hours
   - Reference data: 30 days
   - Structural data: 365 days

2. **Use meaningful cache keys**
   - Include entity type: `product_list`, `customer_list`
   - Include identifier for user-specific data: `user_permissions_{id}`

3. **Keep observers simple**
   - Each observer should invalidate its specific cache only
   - Avoid complex logic in observers

4. **Test after changes**
   - Verify observers fire on model events
   - Verify cache is invalidated correctly
   - Monitor performance metrics

5. **Document cache dependencies**
   - Add comments explaining what cache affects what
   - Update this documentation when adding new caches

## Cache Configuration

### For Development
Use `file` driver (default):
```
CACHE_DRIVER=file
```

### For Production
Use `redis` driver (recommended):
```
CACHE_DRIVER=redis
```

Or `memcached`:
```
CACHE_DRIVER=memcached
```

## Related Files

- `app/Http/Controllers/SaleController.php` - Cache retrieval and invalidation methods
- `app/Providers/AppServiceProvider.php` - Observer registration
- `app/Observers/*` - Individual model observers
- `config/cache.php` - Cache configuration
- `.env` - Cache driver selection

## Future Enhancements

1. **Cache warming**: Populate cache on application startup
2. **Selective invalidation**: Track which users need cache invalidation
3. **Cache profiling**: Track cache hit/miss rates
4. **Conditional caching**: Skip caching for small datasets
5. **Distributed cache**: Share cache across multiple servers
