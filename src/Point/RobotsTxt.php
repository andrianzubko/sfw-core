<?php

namespace App\Point;

class RobotsTxt extends \SFW\Point
{
    public function __construct()
    {
        // {{{ transaction

        $this->sys('Transaction')->run("ISOLATION LEVEL REPEATABLE READ, READ ONLY", null,
            fn() => $this->transactionBody()
        );

        // }}}
        // {{{ making robots.txt

        $this->robots = [];

        $this->robots[] = 'User-agent: *';

        if (self::$config['shared']['robots']) {
            $this->robots[] = 'Allow: /';
        } else {
            $this->robots[] = 'Disallow: /';
        }

        $this->robots[] = sprintf('Host: %s',
            self::$e['defaults']['url']
        );

        if (self::$config['shared']['robots']) {
            $this->robots[] = sprintf('Sitemap: %s/sitemap.xml',
                self::$e['defaults']['url']
            );
        }

        // }}}
        // {{{ output

        $this->sys('Out')->inline(implode("\n", $this->robots) . "\n");

        // }}}
    }

    protected function transactionBody(): bool
    {
        // {{{ environment

        $this->my('Environment')->get();

        // }}}

        return true;
    }
}
