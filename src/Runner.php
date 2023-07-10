<?php

namespace App;

/**
 * Simplest framework runner.
 */
class Runner extends \SFW\Runner
{
    /**
     * Initializing additional environment.
     */
    protected function environment(): void
    {
        // {{{ merge css and js

        $merger = new \SFW\Merger('.merged');

        if (self::$config['sys']['env'] !== 'prod') {
            $merger->recombine(
                [
                    '.css/primary/*.css' => [
                        'all.css',
                        'primary.css',
                    ],
                    '.css/secondary/*.css' => [
                        'all.css',
                        'secondary.css',
                    ],
                    '.js/primary/*.js' => [
                        'all.js',
                        'primary.js',
                    ],
                    '.js/secondary/*.js' => [
                        'all.js',
                        'secondary.js',
                    ],
                ], self::$config['sys']['env'] !== 'debug'
            );
        }

        self::$e['defaults']['merged'] = $merger->get();

        // }}}
        // {{{ these params are only needed in templates

        self::$e['defaults']['request_method'] = $_SERVER['REQUEST_METHOD'];

        if (isset($_REQUEST['REQUEST_URI'])
            && is_scalar($_REQUEST['REQUEST_URI'])
                && mb_check_encoding($_REQUEST['REQUEST_URI'])
        ) {
            self::$e['defaults']['request_uri'] = $_REQUEST['REQUEST_URI'];

            [self::$e['defaults']['request_url'], self::$e['defaults']['request_query']] = [
                ...explode('?', self::$e['defaults']['request_uri'], 2), ''
            ];
        } else {
            self::$e['defaults']['request_uri'] = $_SERVER['REQUEST_URI'];

            self::$e['defaults']['request_url'] = $_SERVER['REQUEST_URL'];

            self::$e['defaults']['request_query'] = $_SERVER['REQUEST_QUERY'];
        }

        // }}}
        // {{{ detect os and device

        if (preg_match('/\b(iphone|ipad|ipod|ios|android|mobile|phone)\b/',
                strtolower($_SERVER['HTTP_USER_AGENT'] ?? ''), $M)
        ) {
            self::$e['defaults']['device'] = 'mobile';

            if ($M[1] === 'android') {
                self::$e['defaults']['os'] = 'android';
            } elseif ($M[1] === 'mobile' || $M[1] === 'phone') {
                self::$e['defaults']['os'] = 'winphone';
            } else {
                self::$e['defaults']['os'] = 'ios';
            }
        } else {
            self::$e['defaults']['device'] = 'desktop';

            self::$e['defaults']['os'] = 'other';
        }

        // }}}
        // {{{ return back url

        if (isset($_REQUEST['r'])
            && is_scalar($_REQUEST['r'])
                && mb_check_encoding($_REQUEST['r'])
        ) {
            self::$e['defaults']['r'] = $this->sys('Text')->fulltrim($_REQUEST['r'], 8192);

            if (self::$e['defaults']['r'] === '') {
                self::$e['defaults']['r'] = '/';
            }
        } else {
            self::$e['defaults']['r'] = '/';
        }

        // }}}
    }
}
