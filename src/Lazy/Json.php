<?php

namespace SFW\Lazy;

/**
 * Json functions.
 */
class Json extends \SFW\Lazy
{
    /**
     * Just in case.
     */
    public function __construct() {}

    /**
     * Decoding some json fields in array. Very comfortably for DB results.
     */
    public function decode(array|false|null $items, array $decodes): array|false|null
    {
        if (!isset($items) || $items === false || !count($items)) {
            return $items;
        }

        if (is_numeric(key($items))) {
            $items_array = &$items;
        } else {
            $items_array = [&$items];
        }

        foreach ($items_array as &$item) {
            foreach ($decodes as $decode) {
                if (isset($item[$decode])) {
                    $item[$decode] = json_decode($item[$decode], true);
                } else {
                    $item[$decode] = [];
                }
            }
        }

        return $items;
    }
}
