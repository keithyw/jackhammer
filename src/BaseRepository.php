<?php
/**
 * Created by PhpStorm.
 * User: keithwatanabe
 * Date: 3/20/15
 * Time: 12:26 PM
 */

namespace Jackhammer;

use Carbon\Carbon;
use Cache;
use Config;
use DB;


/**
 * This will be a version 0.1
 *
 * Roadmap:
 * - change caching to use some form of decorator pattern
 * - better cache management
 * - abstract caching layer
 * - abstract data layer (to allow things like mysql, postgres, etc)
 * - refactor cache key name
 *
 *
 * This class attempts to handle some shortcuts
 * for things like dealing with caching
 * and avoid messy syntax
 *
 * Class BaseRepository
 * @package App\Repositories
 */
class BaseRepository {
    /**
     * gets the cache time in minutes (this is just a convenience method; if you want
     * something more specific, you can roll your own)
     *
     * @param string $configKey
     * @return int
     */
    public function getCacheTime($configKey){
        $time = "repositories.{$configKey}.time";
        if (!Config::has($time)) throw new \Exception("{$time} not defined");
        return Carbon::now()->addMinute(Config::get($time));
    }

    /**
     * Handles the creation of a cache key
     *
     * @param string $configKey
     * @param array $parts
     * @return string
     */
    public function makeCacheKey($configKey, $parts = []){
        return "{$configKey}_" . (is_string($parts) ? '' : join('_', $parts));
    }

    /**
     *
     * Checks if the cache has an item with the $key as part of a tag group
     * as defined by $configKey
     *
     * @param string $configKey
     * @param string $key
     * @return boolean
     */
    public function has($configKey, $key){
        $tag = "repositories.{$configKey}.tag";
        if (!Config::has($tag)) throw new \Exception("{$tag} not defined");
        return Cache::tags(Config::get($tag))->has($key);
    }

    /**
     * Grabs an item from the cache by the $key as part of the tag group $configKey
     * @param string $configKey
     * @param string $key
     * @return mixed
     */
    public function get($configKey, $key){
        return Cache::tags(Config::get("repositories.{$configKey}.tag"))->get($key);
    }

    /**
     * Stores an item $data by the $key as part of the tag group $configKey forever
     *
     * @param string $configKey
     * @param string $key
     * @param mixed $data
     * @return mixed
     */
    public function forever($configKey, $key, $data){
        return Cache::tags(Config::get("repositories.{$configKey}.tag"))->forever($key, $data);
    }
    /**
     *
     * Puts $data into a cache with the $key for a set amount of time
     * as part of a tag group defined by $configKey
     *
     * @param $configKey
     * @param $key
     * @param $data
     * @return mixed
     */
    public function put($configKey, $key, $data){
        return Cache::tags(Config::get("repositories.{$configKey}.tag"))->put($key, $data, $this->getCacheTime($configKey));
    }

    /**
     * Flushes whole groups of the cache
     *
     * @param string $configKey
     * @return mixed
     */
    public function flush($configKey){
        return Cache::tags(Config::get("repositories.{$configKey}.tag"))->flush();
    }

    /**
     * Convenience method for dealing with most find situations
     *
     * @param int $id
     * @param string $cacheTag
     * @param string $obj
     * @param array $load
     * @return mixed|null
     */
    public function find($id, $cacheTag, $obj, $load = null){
        $key = $this->makeCacheKey($cacheTag, [$id]);
        if ($this->has($cacheTag, $key)){
            return $this->get($cacheTag, $key);
        }
        if ($obj = $obj::find($id)){
            if ($load) $obj->load($load);
            $this->put($cacheTag, $key, $obj);
            return $obj;
        }
        return null;
    }

    public function findAndLock($id, $cacheTag, $obj, $load = null){
        $key = $this->makeCacheKey($cacheTag, [$id]);
        if ($this->has($cacheTag, $key)){
            return $this->get($cacheTag, $key);
        }
        // seems odd that cacheTag is the table name but that's how it works
        if ($row = DB::table($cacheTag)->where('id', '=', $id)->sharedLock()->first()){
            $item = $obj::find($row->id);
            if ($load) $item->load($load);
            $this->put($cacheTag, $key, $item);
            return $item;
        }
        return null;
    }

    /**
     * @param $id
     * @param $cacheTag
     * @param $obj
     */
    public function delete($id, $cacheTag, $obj){
        if ($item = $this->find($id, $cacheTag, $obj)){
            $this->flush($cacheTag);
            $item->delete();
            return true;
        }
        return false;
    }

    /**
     * @param int $id
     * @param array $data
     * @param string $cacheTag
     * @param string $obj
     * @return array|bool|mixed|null
     */
    public function update($id, array $data, $cacheTag, $obj){
        $associations = [];
        $many = [];
        $sync = [];
        $dissociations = [];
        if (isset($data['associations'])){
            $associations = $data['associations'];
            unset($data['associations']);
        }
        if (isset($data['has_many'])){
            $many = $data['has_many'];
            unset($data['has_many']);
        }
        if (isset($data['sync'])){
            $sync = $data['sync'];
            unset($data['sync']);
        }
        if (isset($data['dissociations'])){
            $dissociations = $data['dissociations'];
            unset($data['dissociations']);
        }
        if ($item = $this->find($id, $cacheTag, $obj)){
            $item->fill($data);
            if ($item->isValid()){
                foreach ($associations as $type => $val){
                    $item->$type()->associate($val);
                }
                foreach ($dissociations as $type){
                    $item->$type()->dissociate();
                }
                foreach ($many as $type => $val){
                    $item->$type()->save($val);
                }
                foreach ($sync as $type => $val){
                    $item->$type()->sync($val);
                }
                $item->save();

                $this->flush($cacheTag);
                $this->put($cacheTag, $this->makeCacheKey($cacheTag, [$id]), $item);
                return $item;
            }
            return ['errors' => $item->getErrors()];
        }
        return false;
    }

    /**
     * @param array $data
     * @param string $cacheTag
     * @param string $obj
     */
    public function create(array $data, $cacheTag, $obj){
        $item = new $obj();
        if (isset($data['associations'])){
            foreach ($data['associations'] as $type => $val){
                $item->$type()->associate($val);
            }
            unset($data['associations']);
        }
        $many = [];
        $attach = [];
        if (isset($data['has_many'])){
            $many = $data['has_many'];
            unset($data['has_many']);
        }
        if (isset($data['attach'])){
            $attach = $data['attach'];
            unset($data['attach']);
        }
        $item->fill($data);
        if ($item->isValid()){
            $item->save();
            foreach ($many as $type => $val){
                $item->$type()->save($val);
            }
            foreach ($attach as $type => $val){
                $item->$type()->attach($val);
            }
            $this->flush($cacheTag);
            $this->put($cacheTag, $this->makeCacheKey($cacheTag, [$item->id]), $item);
            return $item;
        }
        return ['errors' => $item->getErrors()];
    }

    /**
     * Handles simple where<ColumnName> situations
     *
     * @param string $key
     * @param string $value
     * @param string $cacheTag
     * @param string $obj
     */
    public function findWhere($key, $value, $cacheTag, $obj){
        $cacheKey = $this->makeCacheKey($cacheTag, [$key, $value]);
        if ($this->has($cacheKey, $key)){
            return $this->get($cacheTag, $cacheKey);
        }
        if ($item = $obj::where($key, '=', $value)->first()){
            $this->put($cacheTag, $cacheKey, $item);
            return $item;
        }
        return false;
    }

    /**
     * Loads a collection based on a key items in a list
     *
     * @param array $ids
     * @param string $cacheTag
     * @param string $obj
     */
    public function findMany($ids, $cacheTag, $obj){
        $cacheKey = $this->makeCacheKey($cacheTag, $ids);
        if ($this->has($cacheTag, $cacheKey)){
            return $this->get($cacheTag, $cacheKey);
        }
        if ($items = $obj::findMany($ids)){
            $this->put($cacheTag, $cacheKey, $items);
            return $items;
        }
        return null;
    }

    /**
     * @param string $field
     * @param array $values
     * @return mixed
     */
    public function findManyIn($field, $values, $cacheTag, $obj){
        $parts = $values;
        array_unshift($parts, $field);
        $cacheKey = $this->makeCacheKey($cacheTag, $parts);
        if ($this->has($cacheTag, $cacheKey)){
            return $this->get($cacheTag, $cacheKey);
        }
        if ($items = $obj::whereIn($field, $values)->get()){
            $this->put($cacheTag, $cacheKey, $items);
            return $items;
        }
        return null;
    }

    /**
     * @param array $conditions
     * @param string $cacheTag
     * @param string $obj
     * @params array $load
     * @return mixed
     */
    public function getByConditions($conditions, $cacheTag, $obj, $load = null){
        $parts = [];
        foreach ($conditions as $k => $v){
            $parts[]= $k;
            $parts[]= $v;
        }
        $cacheKey = $this->makeCacheKey($cacheTag, $parts);
        if ($this->has($cacheTag, $cacheKey)){
            return $this->get($cacheTag, $cacheKey);
        }
        $where = join(' AND ', array_map(function($key){
            return "{$key} = ?";
        }, array_keys($conditions)));
        if ($item = $obj::whereRaw($where, array_values($conditions))->get()){
            if ($load) $item->load($load);
            $this->put($cacheTag, $cacheKey, $item);
            return $item;
        }
        return null;
    }

    /**
     * @param array $conditions
     * @param string $cacheTag
     * @param string $obj
     * @params array $load
     * @return mixed
     */
    public function findByConditions($conditions, $cacheTag, $obj, $load = null){
        $parts = [];
        foreach ($conditions as $k => $v){
            $parts[]= $k;
            $parts[]= $v;
        }
        $cacheKey = $this->makeCacheKey($cacheTag, $parts);
        if ($this->has($cacheTag, $cacheKey)){
            return $this->get($cacheTag, $cacheKey);
        }
        $where = join(' AND ', array_map(function($key){
            return "{$key} = ?";
        }, array_keys($conditions)));
        if ($item = $obj::whereRaw($where, array_values($conditions))->first()){
            if ($load) $item->load($load);
            $this->put($cacheTag, $cacheKey, $item);
            return $item;
        }
        return null;
    }

    /**
     * @param string $cacheTag
     * @param string $obj
     * @param int $limit
     * @param int $page
     */
    public function getItems($cacheTag, $obj, $limit = 10, $page = 0){
        $key = $this->makeCacheKey($cacheTag, ['limit', $limit, 'page', $page]);
        if ($this->has($cacheTag, $key)){
            return $this->get($cacheTag, $key);
        }
        $items = $obj::paginate($limit);
        $this->put($cacheTag, $key, $items);
        return $items;
    }

    /**
     * I am evil
     *
     * @param string $cacheTag
     * @param string $obj
     * @param string $sort
     * @param array $load
     * @param int $limit
     * @param int $page
     * @return \Illuminate\Database\Eloquent\Collection $load
     */
    public function load($cacheTag, $obj, $sort = null, $load = null, $limit = null, $page = 0){
        $key = $this->makeCacheKey($cacheTag, ['sort', $sort, 'limit', $limit, 'page', $page]);

        if ($this->has($cacheTag, $key)){
            return $this->get($cacheTag, $key);
        }
        $q = $obj::query();
        if ($sort) $q = $q->orderBy($sort);
        $items = $limit ? $q->paginate($limit) : $q->get();
        if ($load) $items->load($load);
        $this->put($cacheTag, $key, $items);
        return $items;
    }

    /**
     * @param string $cacheTag
     * @param string $obj
     * @return mixed
     */
    public function count($cacheTag, $obj)
    {
        $key = $this->makeCacheKey($cacheTag, $obj, ['count', 1]);
        if ($this->has($cacheTag, $key)){
            return $this->get($cacheTag, $key);
        }
        $count = $obj::count();
        $this->put($cacheTag, $key, $count);
        return $count;
    }

}
