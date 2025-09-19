<?php
namespace OskarKoli\KirbyLitespeed;

use Kirby\Cms\Page;

class KirbyLitespeed {

    public static function purgeAll() {
        header('X-LiteSpeed-Purge: *');
    }

    public static function cache(Page $page) {
        $defaultDuration = option('oskar-koli.kirby-litespeed.default-duration', 172800); // Two days as fallback
        $durationCallable = option('cache.pages.duration');
        if (is_callable($durationCallable)) {
            header('X-LiteSpeed-Cache-Control: public,max-age=' . $durationCallable($page));
        }
        else {
            header('X-LiteSpeed-Cache-Control: public,max-age=' . $defaultDuration);
        }
        
    }
}