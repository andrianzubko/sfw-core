<?php

namespace App;

/**
 * Router.
 */
class Router extends \SFW\Router
{
    /**
     * Route from request url to entry point.
     */
    public function get(): string|false
    {
        if (preg_match('~^((?:/[a-z\d\-]+)*?)(?:/(\d+))?/$~', $_SERVER['REQUEST_URL'], $M)) {
            if ($M[1] === '/index') {
                return false;
            }

            if (isset($M[2])) {
                $_GET['id'] = $_REQUEST['id'] = $M[2];
            }

            if ($M[1] === '') {
                return 'Index';
            }

            return preg_replace_callback('~[\-/](.)~', fn($s) => strtoupper($s[1]), $M[1]);
        }

        if ($_SERVER['REQUEST_URL'] === '/sitemap.xml') {
            return 'SitemapXml';
        }

        if ($_SERVER['REQUEST_URL'] === '/robots.txt') {
            return 'RobotsTxt';
        }

        return false;
    }
}
