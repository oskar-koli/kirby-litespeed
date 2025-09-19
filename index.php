<?php

use Kirby\Cms\App as Kirby;
use Kirby\Cms\Response;
use Kirby\Content\VersionId;
use OskarKoli\KirbyLitespeed\KirbyLitespeed;
use OskarKoli\KirbyLitespeed\LitespeedCache;

@include_once __DIR__ . '/vendor/autoload.php';

Kirby::plugin('oskar-koli/kirby-litespeed', [
    'options' => [
        'purge-token' => null
    ],
    'hooks' => [

        'page.render:after' => function (string $contentType, array $data, string $html, \Kirby\Cms\Page $page) {

            // Note: Taken from Page::render
            $versionId ??= VersionId::$render;
            $versionId ??= $page->renderVersionFromRequest();
            $versionId ??= 'latest';
            $versionId   = VersionId::from($versionId);

            $response = kirby()->response();
            if (
                !$response->usesAuth()
                && !$response->usesCookies()
                && $page->isCacheable($versionId)
            ) {
                KirbyLitespeed::cache($page);
            }
            return $html;
        }
    ],
    'cacheTypes' => [
        'litespeed' => LitespeedCache::class,
    ],
    'routes' => [
        [
            'pattern' => '/litespeed/purge',
            'action'  => function () {
                $purge_token = option('oskar-koli.kirby-litespeed.purge-token');
                if (!$purge_token) {
                    return Response::json([
                        'success' => false,
                        'message' => 'No purge token has been set in site config'
                    ], 401);
                }

                $token = kirby()->request()->get("token");
                if ($purge_token != $token) {
                    return Response::json([
                        'success' => false,
                        'message' => 'Invalid token'
                    ], 401);
                }

                return Response::json([
                    'success' => true
                ], headers: [
                    'X-LiteSpeed-Purge' => '*'
                ]);
            }
        ]
    ]
]);
