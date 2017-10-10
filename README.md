# php-store

## Overview 

The project represents a list of items (store simulation). Each item has following fields:

* `id` - the unique identifier of the item.
* `name` - the name of the item, must be not empty.
* `description` - an optional description of the item.
* `price` - the price of the item. Must be positive decimal.
* `image` - the name of an optional image of the item.

The store may contain up to one million items. More is allowed, but performance degradation is possible.  

To display the items, they are divided into pages (number of items per page is customized).  
The items can be sorted by `id` (default) or `price`, both ascending and descending orders are supported.

New items can be added via form on the main page, every item can be viewed separately 
and can be modified/removed as well.

## Dependencies

* PHP;
* MySQL;
* Memcached;
* php-mysql module for PHP;
* php-memcached module for PHP;
* php-imagick module for PHP.

## Technical details

To support a large amount of items to display and view fast as possible,  
caching and optimization tricks are performed.

### Items caching

Items are cached lazily:

* If a single item has been requested and it's not in cache, 
  it will be loaded from database and put into cache.
* If multiple items were requested (e.g. when a page of items list is opened) 
  the items are tried to load in batch.
  
When a new item is created it's put into cache immediately.  
When an item is updated - it's cache is just invalidated and will be cached on the first opening.  
The invalidation is preferred over cache updates to avoid data races on cache update.

### Sorting caching

This is the biggest challenge: it's necessary to quickly find out which items contains page `N` 
respecting the sorting. Sorting the items in the database on every request is quite expensive 
if you have huge amount of data.  
To deal with it, the sorting could be cached in some way. Following approach is suggested.

The main idea is applying kind of SQRT-decomposition. Keeping the sorted list of all items ids is too expensive, so the items in the database are divided into groups, where each group contains 1000 items 
(last group can have fewer items) and sorting are cached by groups. The group tells us following:
"the order of ids in the group according to sorting field and sorting direction".

Each group has ID of following format:

```
g:$cache_version:$sort_by:$sort_dir:$group_index
```

where:

* `g` - just prefix of key;
* `cache_version` - this is how pages cache is invalidated. When an item is added, deleted or updated - 
the cache version is increased - in other words it's a way of cache invalidation.
* `sort_by` - the field the sorting if performed over (`id` or `price`);
* `sort_dir` - the direction of the sorting (ascending or descending).

Following such caching we are trying to cache only the requested pages and those which are close to it 
(since maximum number of items per page is 100, but group size is 1000);

## Performance

The main goal was to open items page in \< 500 ms with throughput as 1000 requests per minute.  
The best performance is expected when a user walk the pages sequentially, not opening random pages 
(which is possible only by modifying URL address). This is considered as the most real-world scenario, 
since user mostly are interested in first pages (according to the sorting) and don't go to the pages randomly.  
In this cache the pages are effectively cached and the cache hit has very high rate.  
The situation is a little differ when random page are requested, but it's still good on warmed cache.    
To measure the performance, JMeter is used.