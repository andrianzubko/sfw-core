<?php

namespace SFW;

/**
 * Utilities stateless methods.
 */
class Utility extends Base
{
    /**
     * Makes some important cleanups.
     */
    public static function cleanup(): void
    {
        unset(self::$sysLazies['Db']);

        foreach (self::$sysLazies as $lazy) {
            if ($lazy instanceof \SFW\Databaser\Driver
                && $lazy->isInTrans()
            ) {
                try {
                    $lazy->rollback();
                } catch (\SFW\Databaser\Exception) {
                }
            }
        }
    }
}
