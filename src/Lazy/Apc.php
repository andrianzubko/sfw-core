<?php

namespace SFW\Lazy;

/**
 * APC cache.
 */
class Apc extends \SFW\Lazy
{
    /**
     * APC cache module instance.
     */
    public function getInstance(): object
    {
        try {
            $apc = \SFW\SimpleCacher::init('APC', ['prefix' => md5($_SERVER['DOCUMENT_ROOT'])]);
        } catch (\SFW\SimpleCacher\Exception $error) {
            $this->abend()->error($error->getMessage());
        }

        return $apc;
    }
}
