<?php
/**
 * Created by PhpStorm.
 * User: keithwatanabe
 * Date: 7/15/17
 * Time: 11:05 PM
 */

namespace Jackhammer\Cache;

use Cache;
use Config;

/**
 * Class RedisCache
 * @package App\Cache
 */
class RedisCache extends AbstractCache
{
    /**
     * @inheritdoc
     */
    public function get($configKey, $key)
    {
        return Cache::tags(Config::get("repositories.{$configKey}.tag"))->get($key);
    }

    /**
     * @inheritdoc
     */
    function has($configKey, $key)
    {
        $tag = "repositories.{$configKey}.tag";
        if (!Config::has($tag)) throw new \Exception("{$tag} not defined");
        return Cache::tags(Config::get($tag))->has($key);
    }

    /**
     * @inheritdoc
     */
    public function forever($configKey, $key, $data)
    {
        return Cache::tags(Config::get("repositories.{$configKey}.tag"))->forever($key, $data);
    }

    /**
     * @inheritdoc
     */
    public function flush($configKey)
    {
        return Cache::tags(Config::get("repositories.{$configKey}.tag"))->flush();
    }

    /**
     * @inheritdoc
     */
    public function put($configKey, $key, $data)
    {
        return Cache::tags(Config::get("repositories.{$configKey}.tag"))->put($key, $data, $this->getCacheTime($configKey));
    }

}