# Kirby Litespeed Plugin

[![Packagist Version](https://img.shields.io/packagist/v/oskar-koli/kirby-litespeed)](https://packagist.org/packages/oskar-koli/kirby-litespeed)

Adds support for using Litespeed's LSCache for page caching in Kirby CMS.

Kirby's built in caching still requires Kirby to boot for the cached pages to be returned, which results in even cached requests usually taking at least 150ms on shared hosting. Using this plugin Kirby doesn't have to be booted when a cached page is hit, resulting in 70ms or faster response times.

## Installation & Configuration

### Requirements

- Kirby CMS ^5
- Litespeed server (The site will still work without a Litespeed server, but then this plugin does nothing)

### Installation

```bash
composer require oskar-koli/kirby-litespeed
```

### Configuration

```php
// site/config/config.php
'cache' => [
    'pages' => [
        'type' => 'litespeed',
        'active' => true,

        // Optional:

        // Controls if page should be cached
        'ignore' => fn ($page) => $page->title()->value() === 'Do not cache me',

        // Controls how long Litespeed caches the page (in seconds)
        // The default duration is 2 days (172800 seconds)
        'duration' => fn ($page) => $page->slug() == 'slug' ? 172800 : 600
    ]
]
```

> **Note:** The Litespeed cache can only be used for pages and will *not* work as the driver for any other kind of cache.

## Caching & Purging Logic

A page is cached as long as the request
- Is a GET or HEAD request
- The content type is HTML
- Doesn't use any cookies (e.g. doesn't use `kirby()->user()`, `Cookie:set` or `Cookie:get`)
- Doesn't contain `Authorization` headers

For more details see `\Kirby\Cms\Page::isCacheable`.

The cache is purged when
- Kirby requests a purge, e.g. when any edits are made in the Panel (this causes a full purge)
- The max-age of a specific page's cache is reached (handled by Litespeed)

## .htaccess Configuration

The plugin does not modify the `.htaccess` automatically. You need to enable Litespeed caching manually by for example adding the following configuration:

```apache
<IfModule LiteSpeed>
    RewriteEngine on
    CacheLookup on
    RewriteRule .* - [E=Cache-Control:no-autoflush]
    
    # Ignore some common query parameters
    CacheKeyModify -qs:fbclid
    CacheKeyModify -qs:gclid
    CacheKeyModify -qs:utm*
    CacheKeyModify -qs:_ga
</IfModule>
```

## CLI Cache Purging

You can manually clear the cache using Kirby CLI:

```bash
kirby clear:cache pages
```

This works by making an HTTP request to a route on the website which triggers the Litespeed server to purge the cache.

For CLI purging to work, you need to define a purge token in your `config.php`:

```php
// Token used to authenticate the REST call which purges the cache
'oskar-koli.kirby-litespeed.purge-token' => 'your-secure-token-here'
```
You can for example generate the token by running `openssl rand -hex 32`.

Additionally, the plugin  needs to know the URL of your site. You have three options:

#### Option 1: Plugin specific option
```php
'oskar-koli.kirby-litespeed.site-url' => 'https://example.com'
```

#### Option 2: Environment variable
```bash
LITESPEED_SITE_URL="https://example.com" kirby clear:cache pages
```

#### Option 3: Kirby's URL option
If none of these are set, the plugin will fall back to `site()->url()`. <br/>
For this to work in CLI, the `url` option has to be set:
```php
'url' => 'https://example.com'
```

## Is the plugin production ready?

The plugin is in use in production on a couple smaller websites and everything is working smoothly. That being said, there might still be some edge cases that which might cause issues on more complex websites. Please submit a bug report if you face any issues!
