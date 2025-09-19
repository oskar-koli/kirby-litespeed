<?php
namespace OskarKoli\KirbyLitespeed;

use Kirby\Cms\Page;

class KirbyLitespeed {

    public static function purgeAll() {
        header('X-LiteSpeed-Purge: *');
    }

    public static function cache(Page $page) {
        header('X-LiteSpeed-Cache-Control: public,max-age=172800');
    }
}