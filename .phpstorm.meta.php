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
        'Locker',
        'Logger',
        'Memcached',
        'Notifier',
        'Number',
        'Out',
        'Paginator',
        'Templater',
        'Text',
        'Transaction',
    );

    override(\SFW\Base::sys(0), map([
        '' => 'App\Lazy\My\@|\SFW\Lazy\Sys\@',
    ]));
}
