# Kirby Litespeed Plugin
![Packagist Version](https://img.shields.io/packagist/v/oskar-koli/kirby-litespeed)

Adds support for Litespeed page caching to Kirby CMS.

## Installation & Configuration

Install the plugin using composer:
```
composer require oskar-koli/kirby-litespeed
```

Configure the page cache to use it:
```php
'cache' => [
    'pages' => [
        'type'   => 'litespeed',
        'active' => true
    ]
]
```
Note: The litespeed cache can only be used for pages and will *not* work as the driver for any other kind of cache.

## Caching logic
The caching logic is handled the same way as any other Kirby cache driver. So any use of cookies will for example stop a page from being cached. You can also control if a page should be cached and for how long using the 'ignore' and 'duration' options:
```php
'cache' => [
    'pages' => [
        'type'   => 'litespeed',
        'active' => true,

        // Controls if page should be cached
        'ignore' => fn ($page) => $page->title()->value() === 'Do not cache me',
        
        // Max caching duration in seconds
        'duration' => fn ($page) => $page->slug() == 'a' ? 172800 : 600
    ]
]
```

## Purging using CLI
You can manually clear the cache using the Kirby CLI:
```bash
kirby clear:cache pages
```

This works by making an HTTP request to a route on the website which triggers the Litespeed server to purge the cache.

For this to work you have to define the purge-token in the config.php:
```php
// Token used to authenticate the REST call which purges the cache
'oskar-koli.kirby-litespeed.purge-token' => "Some secure token"
```

In addition, plugin needs to know the url of the site:
```php
'oskar-koli.kirby-litespeed.site-url' => 'https://example.com'
```
or else pass the targer url as an environment variable:
```bash
LITESPEED_SITE_URL="https://example.com" kirby clear:cache pages
```
If neither one is present, then `site()->url()` is used which only works in CLI if the url option is set:
```php
'url' => 'https://example.com'
```