<?php
/**
 * Created by PhpStorm.
 * User: keithwatanabe
 * Date: 7/15/17
 * Time: 10:53 PM
 */

namespace Jackhammer\Contracts\Cache;

interface Cache
{
    /**
     * gets the cache time in minutes (this is just a convenience method; if you want
     * something more specific, you can roll your own)
     *
     * @param string $configKey
     * @throws \Exception
     * @return int
     */
    public function getCacheTime($configKey);

    /**
     * Handles the creation of a cache key
     *
     * @param string $configKey
     * @param array $parts
     *
     * @return string
     */
    public function makeCacheKey($configKey, $parts = []);

    /**
     * Grabs an item from the cache by the $key as part of the tag group $configKey
     * @param string $configKey
     * @param string $key
     * @return mixed
     */
    public function get($configKey, $key);

    /**
     *
     * Checks if the cache has an item with the $key as part of a tag group
     * as defined by $configKey
     *
     * @param string $configKey
     * @param string $key
     * @return boolean
     */
    public function has($configKey, $key);

    /**
     * Stores an item $data by the $key as part of the tag group $configKey forever
     *
     * @param string $configKey
     * @param string $key
     * @param mixed $data
     * @return mixed
     */
    public function forever($configKey, $key, $data);

    /**
     * Flushes whole groups of the cache
     *
     * @param string $configKey
     * @return mixed
     */
    public function flush($configKey);

    /**
     *
     * Puts $data into a cache with the $key for a set amount of time
     * as part of a tag group defined by $configKey
     *
     * @param $configKey
     * @param $key
     * @param $data
     * @return mixed
     * @throws \Exception
     */
    public function put($configKey, $key, $data);
}