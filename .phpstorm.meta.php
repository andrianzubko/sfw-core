<?php

namespace PHPSTORM_META {
    override(\SFW\Lazy\SysCaller::__call(), map([
        'db' => \SFW\Databaser\Driver::class,
        'pgsql' => \SFW\Databaser\Driver::class,
        'mysql' => \SFW\Databaser\Driver::class,
        'cache' => \SFW\SimpleCacher\Cache::class,
        'apc' => \SFW\SimpleCacher\Cache::class,
        'memcached' => \SFW\SimpleCacher\Cache::class,
        '' => '\App\Lazy\Sys\@|\SFW\Lazy\Sys\@',
    ]));
}
