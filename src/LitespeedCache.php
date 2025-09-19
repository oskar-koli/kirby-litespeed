<?php
namespace OskarKoli\KirbyLitespeed;

use Exception;
use Kirby\Cache\Cache;
use Kirby\Cache\Value;
use Kirby\Http\Remote;

class LitespeedCache extends Cache {
    public function flush(): bool {
        if (kirby()->request()->cli()) {

            
            $target_url = kirby()->environment()->get("LITESPEED_SITE_URL", 
                option('oskar-koli.kirby-litespeed.site-url', site()->url()));
            if (!$target_url || $target_url == '/') {
                throw new Exception('You either have to pass the LITESPEED_SITE_URL environment variable or set the \'url\' config option for purging to work through the CLI!');
            }
            
            $remote = Remote::request($target_url . '/litespeed/purge?token=' . option('oskar-koli.kirby-litespeed.purge-token'));
            $response = $remote->json();
            $success = $response["success"] ?? false;
            if (!$success) {
                $message = $response["message"] ?? "Unknown reason";
                $status = $remote->info()["http_code"] ?? -1;
                throw new Exception("Failed to clear Litespeed cache. Reason = \"" . $message . "\", status = " . $status);
            }
        }
        else {
            KirbyLitespeed::purgeAll();
        }
        return true;
    }

    public function remove(string $key): bool {
        // Noop
        return true;
    }

    public function retrieve(string $key): ?Value {
        // Noop
        return null;
    }

    public function set(string $key, $value, int $minutes = 0): bool {
        // Noop
        return false;
    }

}