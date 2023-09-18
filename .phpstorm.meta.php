<?php

namespace PHPSTORM_META {
    expectedArguments(\SFW\Base::sys(0), 0,
        'Apc',
        'Cacher',
        'Curl',
        'Db',
        'Dir',
        'File',
        'Image',
        'Locker',
        'Logger',
        'Memcached',
        'Mysql',
        'Native',
        'Nocache',
        'Notifier',
        'Number',
        'Paginator',
        'Pgsql',
        'Redis',
        'Response',
        'Shutdown',
        'Templater',
        'Text',
        'Transaction',
        'Xslt',
    );

    override(\SFW\Base::sys(0), map([
        '' => 'App\Lazy\My\@|\SFW\Lazy\Sys\@',
    ]));
}
