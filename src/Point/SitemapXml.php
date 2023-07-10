<?php

namespace App\Point;

class SitemapXml extends \SFW\Point
{
    public function __construct()
    {
        // {{{ transaction

        $this->sys('Transaction')->run("ISOLATION LEVEL REPEATABLE READ, READ ONLY", null,
            fn() => $this->transactionBody()
        );

        // }}}
        // {{{ making sitemap

        $this->sitemap = new \SimpleXMLElement(
            '<?xml version="1.0" encoding="utf-8"?><urlset />'
        );

        $this->sitemap['xmlns'] = 'https://www.sitemaps.org/schemas/sitemap/0.9';

        $this->sitemap->addChild('url')->loc = self::$e['defaults']['url'];

        // }}}
        // {{{ output

        $this->sys('Out')->inline($this->sitemap->asXML(), 'text/xml');

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
