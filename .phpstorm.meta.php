<?php

namespace PHPSTORM_META {
    expectedArguments(\SFW\Base::sys(0), 0,
        'Apc',
        'Cacher',
        'Curl',
        'Db',
        'Dir',
        'Dispatcher',
        'File',
        'Image',
        'Locker',
        'Logger',
        'Mailer',
        'Memcached',
        'Mysql',
        'Native',
        'Nocache',
        'Notifier',
        'Paginator',
        'Pgsql',
        'Provider',
        'Redis',
        'Response',
        'Router',
        'Templater',
        'Text',
        'Transaction',
        'Twig',
        'Xslt',
    );

    override(\SFW\Base::sys(0), map([
        '' => 'App\Lazy\My\@|\SFW\Lazy\Sys\@',
    ]));

    override(\App\Lazy\Sys\Dispatcher::dispatch(0), type(0));
    override(\SFW\Lazy\Sys\Dispatcher::dispatch(0), type(0));
}
