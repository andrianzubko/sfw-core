<?php

declare(strict_types=1);

namespace SFW;

/**
 * Abstraction for routers.
 */
abstract class Router extends Base
{
    /**
     * Internal cache.
     */
    protected static array|false $cache;

    /**
     * Classes files.
     */
    private static array $classesFiles;

    /**
     * Classes loaded flag.
     */
    private static bool $classesLoaded = false;

    /**
     * Reads and actualizes cache if needed.
     *
     * @throws Exception\Runtime
     */
    protected function readCache(string $cacheFile): void
    {
        static::$cache = @include $cacheFile;

        if (static::$cache !== false
            && (self::$sys['config']['env'] === 'prod' || $this->isCacheActual())
        ) {
            return;
        }

        $this->rebuildCache($this->initNewCache());

        $this->saveCache($cacheFile);
    }

    /**
     * Rebuilds cache.
     */
    abstract protected function rebuildCache(array $initialCache): void;

    /**
     * Initializes new cache.
     */
    private function initNewCache(): array
    {
        $this->loadAllClasses();

        $cache = [];

        $cache['time'] = time();

        $cache['count'] = \count(self::$classesFiles);

        return $cache;
    }

    /**
     * Checks cache relevance.
     */
    private function isCacheActual(): bool
    {
        $this->findAllClasses();

        if (\count(self::$classesFiles) !== static::$cache['count']) {
            return false;
        }

        foreach (self::$classesFiles as $file) {
            if ((int) filemtime($file) > static::$cache['time']) {
                return false;
            }
        }

        return true;
    }

    /**
     * Saves cache.
     *
     * @throws Exception\Runtime
     */
    private function saveCache(string $cacheFile): void
    {
        if (!self::sys('File')->putVar($cacheFile, static::$cache, LOCK_EX)) {
            throw new Exception\Runtime("Unable to write file $cacheFile");
        }
    }

    /**
     * Finds all classes.
     */
    private function findAllClasses(): void
    {
        if (isset(self::$classesFiles)) {
            return;
        }

        self::$classesFiles = [];

        foreach (self::sys('Dir')->scan(APP_DIR . '/src', true, true) as $item) {
            if (is_file($item)
                && str_ends_with($item, '.php')
            ) {
                self::$classesFiles[] = $item;
            }
        }
    }

    /**
     * Loads all classes.
     */
    private function loadAllClasses(): void
    {
        if (self::$classesLoaded) {
            return;
        }

        $this->findAllClasses();

        foreach (self::$classesFiles as $file) {
            require_once $file;
        }

        self::$classesLoaded = true;
    }
}
