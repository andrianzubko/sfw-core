<?php

declare(strict_types=1);

namespace SFW;

/**
 * Registry of some classes.
 */
abstract class Registry extends Base
{
    /**
     * Internal cache file.
     */
    private string $cacheFile;

    /**
     * Internal cache.
     */
    protected array|false $cache;

    /**
     * Scanned PHP files.
     */
    private static array $phpFiles;

    /**
     * Scanned PHP files loaded or not yet.
     */
    private static bool $phpFilesLoaded = false;

    /**
     * Checks and actualize cache if needed.
     *
     * @throws Exception\Runtime
     */
    public function __construct(string $cacheFile)
    {
        $this->cacheFile = $cacheFile;

        $this->cache = @include $this->cacheFile;

        if ($this->cache === false || self::$sys['config']['env'] !== 'prod' && $this->isOutdated()) {
            $this->rebuild();
        }
    }

    /**
     * Gets cache.
     */
    public function getCache(): array
    {
        return $this->cache;
    }

    /**
     * Scanning for PHP files.
     */
    private function scanForPhpFiles(): void
    {
        if (isset(self::$phpFiles)) {
            return;
        }

        self::$phpFiles = [];

        foreach (self::sys('Dir')->scan(APP_DIR . '/src', true, true) as $item) {
            if (is_file($item) && str_ends_with($item, '.php')) {
                self::$phpFiles[] = $item;
            }
        }
    }

    /**
     * Rechecks of the needs for cache rebuild.
     */
    private function isOutdated(): bool
    {
        $this->scanForPhpFiles();

        if ($this->cache['count'] !== \count(self::$phpFiles)) {
            return true;
        }

        foreach (self::$phpFiles as $file) {
            if ((int) filemtime($file) > $this->cache['time']) {
                return true;
            }
        }

        return false;
    }

    /**
     * Rescans files and rebuilds cache.
     *
     * @throws Exception\Runtime
     */
    private function rebuild(): void
    {
        if (!self::$phpFilesLoaded) {
            $this->scanForPhpFiles();

            foreach (self::$phpFiles as $file) {
                require_once $file;
            }

            self::$phpFilesLoaded = true;
        }

        $this->rebuildCache();

        $this->cache['time'] = time();

        $this->cache['count'] = \count(self::$phpFiles);

        if (!self::sys('File')->putVar($this->cacheFile, $this->cache, LOCK_EX)) {
            throw new Exception\Runtime("Unable to write file $this->cacheFile");
        }
    }

    /**
     * Rebuilds cache.
     *
     * @throws Exception\Runtime
     */
    abstract protected function rebuildCache(): void;
}
