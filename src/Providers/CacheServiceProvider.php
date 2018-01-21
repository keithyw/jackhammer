<?php
/**
 * Created by PhpStorm.
 * User: keithwatanabe
 * Date: 1/21/18
 * Time: 10:52 AM
 */

namespace Jackhammer\Providers;

use Config;
use Illuminate\Support\ServiceProvider;

/**
 * Class CacheServiceProvider
 * @package Jackhammer\Providers
 */
class CacheServiceProvider extends ServiceProvider
{
    /**
     *
     */
    public function register()
    {
        $this->app->bind('Jackhammer\Contracts\Cache\Cache', Config::get('jackhammer.cache_type'));
    }

}