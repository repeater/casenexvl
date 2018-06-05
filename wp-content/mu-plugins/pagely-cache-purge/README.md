Public API
---

### Hooks for purging cache for everything/specific objects, used via `apply_filters`:

- `pagely_purge_all`
- `pagely_purge_comment`
- `pagely_purge_post`
- `pagely_purge_common` (purge `/`, `/(.*)(/?)feed(.*)`, and the contents of the `$PAGELY_CACHE_PURGE_ALWAYS` global)

### Hooks for changing cache purge behavior, used via `add_filter`:

- `pagely_cache_purge_path`
  - To disable purging `return null;` from the filter (useful during batch imports, etc)
 
The `pagely-cache-purge.php:purgePath()` function is the main hook which checks & stops all subsequent PURGE requests.

---

## Disable all caching from the site by mu-plugin hook

Example code:
- For website to put in their automated import function.
```php
<?php
// disable cache purge
add_filter('pagely_cache_purge_path', function($url) { return null; });
```
---

## Purge all cache function example
- If you need to purge all cache
```
if ( class_exists( 'PagelyCachePurge' ) ) {
        $purger = new PagelyCachePurge();
        $purger->purgeAll();
}
```
---

## Stop the homepage from PURGING, when only comments are made
```
# Create mu-script
$ sudo -u www-data nano wp-content/mu-plugins/remove-homepage-purge-when-comments-posted.php
```

Content:
```php
<?php
// disable cache purge on comments from happening on homepage
add_filter('pagely_cache_purge_path', function($url) {
        if ($url == "/") {
            // Only stop homepage from purging
            return null;
        } else {
            return $url;
        }
});
```



---

## Add additional pages to be purged on any purge

Use case when there's /latest/ or /topic/* additonal pages which show latest posts.

```
wp-content/mu-plugins/custom-global-purge-pages.php
```
```php
<?php
/*
Plugin Name: Pagely Cache Purge global pages
Description: Custom list to add pages in addition to the ones already queued up to be purged.
Author: Pagely
Author URI: http://pagely.com
Version: 1.0
*/

$PAGELY_CACHE_PURGE_ALWAYS = [
    '/latest/',
    '/topics/(.*)',
];
```
Setting the `$PAGELY_CACHE_PURGE_ALWAYS` array should make use of what already exists in the management script by including the global variable, to purge more pages for every purge request.

Test it out to confirm working correctly.
