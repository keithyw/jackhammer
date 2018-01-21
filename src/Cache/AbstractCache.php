<?php

namespace Jackhammer\Cache;

use Carbon\Carbon;
use Config;

/**
 * Created by PhpStorm.
 * User: keithwatanabe
 * Date: 7/15/17
 * Time: 10:52 PM
 */
abstract class AbstractCache implements CacheInterface
{
    /**
     * @inheritdoc
     */
    public function getCacheTime($configKey)
    {
        $time = "repositories.{$configKey}.time";
        if (!Config::has($time)) throw new \Exception("{$time} not defined");
        return Carbon::now()->addMinute(Config::get($time));
    }

    /**
     * @inheritdoc
     */
    public function makeCacheKey($configKey, $parts = [])
    {
        return "{$configKey}_" . (is_string($parts) ? '' : join('_', $parts));
    }

    /**
     * @inheritdoc
     */
    abstract public function get($configKey, $key);

    /**
     * @inheritdoc
     */
    abstract function has($configKey, $key);

    /**
     * @inheritdoc
     */
    abstract public function forever($configKey, $key, $data);

    /**
     * @inheritdoc
     */
    abstract public function flush($configKey);

    /**
     * @inheritdoc
     */
    abstract public function put($configKey, $key, $data);
}