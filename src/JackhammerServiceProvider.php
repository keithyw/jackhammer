<?php

namespace Jackhammer;
/**
 * Created by PhpStorm.
 * User: keithwatanabe
 * Date: 9/23/15
 * Time: 10:25 AM
 */

use Illuminate\Support\ServiceProvider;

class JackhammerServiceProvider extends ServiceProvider {

    public function boot()
    {

    }

    public function register()
    {
        $this->loadViewsFrom(__DIR__ . '/resources/views', 'jackhammer');
        $this->publishes([
            __DIR__ . '/config/jackhammer.php' => config_path('jackhammer.php'),
            __DIR__ . '/config/repositories.php' => config_path('repositories.php'),
            __DIR__ . '/resources/views/admin.blade.php' => base_path('resources/views/admin.blade.php')
        ]);
    }
}