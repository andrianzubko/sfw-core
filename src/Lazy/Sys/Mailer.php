<?php
declare(strict_types=1);

namespace SFW\Lazy\Sys;

/**
 * Mailer.
 */
class Mailer extends \SFW\Lazy\Sys
{
    /**
     * Just a placeholder.
     *
     * If your overrides constructor, don't forget call parent at first line! Even if it's empty!
     */
    public function __construct()
    {
    }

    /**
     * Creates new email.
     *
     * @throws \SFW\Exception
     */
    public function create(bool $strict = false): \SFW\Mailer
    {
        return new \SFW\Mailer($strict);
    }
}
