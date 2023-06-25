<?php

namespace SFW;

/**
 * Basic abstract for all Lazy classes.
 */
abstract class Lazy extends Base
{
    /**
     * By default it self instance, but can be changed to any other in some lazy classes.
     */
    public function getInstance(): object
    {
        return $this;
    }
}
