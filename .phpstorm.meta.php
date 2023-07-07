<?php

namespace PHPSTORM_META {
    expectedArguments(\SFW\Base::sys(0), 0,
        'Abend',
        'Apc',
        'Cache',
        'Curl',
        'Db',
        'Dir',
        'File',
        'Image',
        'Json',
        'Locker',
        'Logger',
        'Memcached',
        'Mysql',
        'Notifier',
        'Number',
        'Out',
        'Paginator',
        'Pgsql',
        'Templater',
        'Text',
        'Transaction',
    );

    override(\SFW\Base::sys(0), map([
        '' => 'App\Lazy\My\@|\SFW\Lazy\Sys\@',
    ]));
}