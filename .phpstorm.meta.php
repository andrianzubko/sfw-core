<?php

namespace PHPSTORM_META {
    expectedArguments(\SFW\Base::sys(0), 0,
        'Abend',
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
        'Out',
        'Out:Native',
        'Out:Xslt',
        'Paginator',
        'Pgsql',
        'Redis',
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
