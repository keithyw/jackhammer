<?php
/**
 * Created by PhpStorm.
 * User: keithwatanabe
 * Date: 9/22/15
 * Time: 4:59 PM
 */

return [
    'cache_type' => 'Jackhammer\Cache\RedisCache',
    'contracts' => 'Contracts',
    'default_limit' => 10,
    'form_requests' => 'Http\Requests',
    'models' => 'Models',
    'rest_controllers' => 'Http\Controllers\Rest',
    'repositories' => 'Repositories',
    'transformers' => 'Transformers',
];