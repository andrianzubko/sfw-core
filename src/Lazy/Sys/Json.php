<?php

namespace SFW\Lazy\Sys;

/**
 * Json functions.
 */
class Json extends \SFW\Lazy\Sys
{
    /**
     * Decoding some json fields in array. Very comfortably for DB results.
     */
    public function decode(array|false|null $items, array $decodes): array|false|null
    {
        if (!isset($items)
            || $items === false
                || !count($items)
        ) {
            return $items;
        }

        if (is_numeric(key($items))) {
            $array = &$items;
        } else {
            $array = [&$items];
        }

        foreach ($array as &$item) {
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
