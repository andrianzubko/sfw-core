<?php

namespace App\Config;

/**
 * Your config (not available from templates).
 */
class My extends \SFW\Config
{
    /**
     * Returns array with config parameters.
     */
    public static function get(): array
    {
        $my = [];

        // {{{ notifier

        /* Default sender.
         *
         * 'EMAIL' or array('EMAIL'[, 'NAME'])
         */
        $my['notifier']['sender'] = ['example@domain.com', 'Sender'];

        /* Default replies.
         *
         * array('EMAIL' or array('EMAIL'[, 'NAME']), ...)
         */
        $my['notifier']['replies'] = [];

        // }}}

        return $my;
    }
}
