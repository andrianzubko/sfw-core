<?php

namespace SFW\Lazy\Sys;

/**
 * Redis.
 *
 * @mixin \SFW\Cacher\Driver
 */
class Redis extends \SFW\Lazy\Sys
{
    /**
     * Options for cacher.
     */
    protected array $options;

    /**
     * Initializes options for cacher.
     *
     * If your overrides constructor, don't forget call parent at first line!
     */
    protected function __construct()
    {
        $this->options = [
            'ttl' => self::$config['sys']['cacher_redis_ttl'],

            'ns' => self::$config['sys']['cacher_redis_ns'],

            'connect' => self::$config['sys']['cacher_redis_connect'],

            'options' => self::$config['sys']['cacher_redis_options'],
        ];
    }

    /**
     * Redis module instance.
     *
     * @internal
     */
    public static function getInstance(): \SFW\Cacher\Driver
    {
        return new \SFW\Cacher\Redis((new static())->options);
    }
}
