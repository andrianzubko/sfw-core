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
        'Response:Native',
        'Response:Xslt',
        'Templater',
        'Text',
        'Transaction',
        'Transaction:Mysql',
        'Transaction:Pgsql',
        'Xslt',
    );

    override(\SFW\Base::sys(0), map([
        '' => 'App\Lazy\My\@|\SFW\Lazy\Sys\@',
    ]));
}
