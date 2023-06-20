<?php

namespace SFW\Lazy;

/**
 * Dir functions.
 */
class Dir extends \SFW\Lazy
{
    /**
     * For temporary directory.
     */
    protected ?string $temporary;

    /**
     * Directory creation.
     */
    public function create(string $target): bool
    {
        if (!is_dir($target)) {
            return mkdir($target, 0777, true);
        }

        return true;
    }

    /**
     * Directory recreation.
     */
    public function recreate(string $target): bool
    {
        if (!$this->dir()->remove($target)) {
            return false;
        }

        return mkdir($target, 0777, true);
    }

    /**
     * Directory removing.
     */
    public function remove(string $target, bool $recursive = true): bool
    {
        $status = true;

        if (is_dir($target)) {
            if ($recursive) {
                if (($items = scandir($target)) !== false) {
                    foreach ($items as $item) {
                        if ($item !== '.' && $item !== '..') {
                            if (is_dir("$target/$item")) {
                                if ($this->remove("$target/$item", true) === false) {
                                    $status = false;
                                }
                            } elseif (unlink("$target/$item") === false) {
                                $status = false;
                            }
                        }
                    }
                } else {
                    $status = false;
                }
            }

            if (rmdir($target) === false) {
                $status = false;
            }
        }

        return $status;
    }

    /**
     * Directory coping.
     */
    public function copy(string $source, string $target): bool
    {
        $status = true;

        if (is_dir($source)
            && $this->dir()->create($target) !== false
                && ($items = scandir($source)) !== false
        ) {
            foreach ($items as $item) {
                if ($item !== '.' && $item !== '..') {
                    if (is_dir("$source/$item")) {
                        if ($this->copy("$source/$item", "$target/$item") === false) {
                            $status = false;
                        }
                    } elseif (copy("$source/$item", "$target/$item") === false) {
                        $status = false;
                    }
                }
            }
        } else {
            $status = false;
        }

        return $status;
    }

    /**
     * Making temporary directory.
     */
    public function temporary(): string
    {
        if (!isset($this->temporary)) {
            $this->temporary = realpath(sys_get_temp_dir());
        }

        for ($i = 1; $i <= 10; ++$i) {
            $dir = sprintf('%s/%s', $this->temporary, $this->text()->random());

            if (mkdir($dir, 0644, true)) {
                register_shutdown_function(
                    function () use ($dir) {
                        $this->remove($dir);
                    }
                );

                return $dir;
            }
        }

        $this->abend()->error();
    }
}
