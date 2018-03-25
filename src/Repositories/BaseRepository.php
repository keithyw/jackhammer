<?php
/**
 * Created by PhpStorm.
 * User: keithwatanabe
 * Date: 3/20/15
 * Time: 12:26 PM
 */

namespace Jackhammer\Repositories;

use Carbon\Carbon;
use Config;
use DB;
use Jackhammer\Models\BaseModel;
use Jackhammer\Contracts\BaseRepository as BaseRepositoryInterface;


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
 * @package Jackhammer\Repositories
 */
class BaseRepository implements BaseRepositoryInterface
{

    /**
     * @var \Jackhammer\Contracts\Cache\Cache
     */
    private $cache;

    /**
     * @var \Jackhammer\Models\BaseModel
     */
    private $model;

    /**
     * BaseRepository constructor.
     * @param BaseModel $model
     */
    public function __construct($model)
    {
        $this->cache = resolve('Jackhammer\Contracts\Cache\Cache');
        $this->model = $model;
    }

    /**
     * @inheritdoc
     */
    public function getTable()
    {
        return $this->model->getTable();
    }

    /**
     * @inheritdoc
     */
    public function getModel()
    {
        return $this->model;
    }

    /**
     * @return string
     */
    public function makeCacheTag()
    {
        return $this->model->getTable();
    }

    /**
     * @return string
     */
    public function getModelName()
    {
        return get_class($this->model);
    }


    /**
     * @inheritdoc
     */
    public function find($id, $load = null){
        $cacheTag = $this->makeCacheTag();
        $obj = $this->getModelName();
        $key = $this->cache->makeCacheKey($cacheTag, [$id]);
        if ($this->cache->has($cacheTag, $key)){
            return $this->cache->get($cacheTag, $key);
        }
        if ($obj = $obj::find($id)){
            if ($load) $obj->load($load);
            $this->cache->put($cacheTag, $key, $obj);
            return $obj;
        }
        return null;
    }

    /**
     * @inheritdoc
     */
    public function findAndLock($id, $load = null){
        $cacheTag = $this->makeCacheTag();
        $obj = $this->getModelName();
        $key = $this->cache->makeCacheKey($cacheTag, [$id]);
        if ($this->cache->has($cacheTag, $key)){
            return $this->cache->get($cacheTag, $key);
        }
        // seems odd that cacheTag is the table name but that's how it works
        if ($row = DB::table($cacheTag)->where('id', '=', $id)->sharedLock()->first()){
            $item = $obj::find($row->id);
            if ($load) $item->load($load);
            $this->cache->put($cacheTag, $key, $item);
            return $item;
        }
        return null;
    }

    /**
     * @inheritdoc
     */
    public function delete($id){
        $cacheTag = $this->makeCacheTag();
        $obj = $this->getModelName();
        if ($item = $this->find($id, $cacheTag, $obj)){
            $this->cache->flush($cacheTag);
            $item->delete();
            return true;
        }
        return false;
    }

    /**
     * @inheritdoc
     */
    public function update($id, array $data){
        $cacheTag = $this->makeCacheTag();
        $obj = $this->getModelName();
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
        // maybe i should just change this to either a refresh or raw find
        //if ($item = $this->find($id, $cacheTag, $obj)){
        if ($item = $obj::find($id)){
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
                /**
                 * @todo need to handle this $res variable in some capacity
                 */
                $res = $item->save();
                $this->cache->flush($cacheTag);
                $this->cache->put($cacheTag, $this->cache->makeCacheKey($cacheTag, [$id]), $item);
                return $item;
            }
            throw new RepositoryException(implode(',', $item->getErrors()));
        }
        return false;
    }

    /**
     * @inheritdoc
     */
    public function create(array $data){
        $cacheTag = $this->makeCacheTag();
        $obj = $this->getModelName();
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
            $this->cache->flush($cacheTag);
            $this->cache->put($cacheTag, $this->cache->makeCacheKey($cacheTag, [$item->id]), $item);
            return $item;
        }
        throw new RepositoryException(implode(',', $item->getErrors()));
    }

    /**
     * @inheritdoc
     */
    public function findWhere($key, $value){
        $cacheTag = $this->makeCacheTag();
        $obj = $this->getModelName();
        $cacheKey = $this->cache->makeCacheKey($cacheTag, [$key, $value]);
        if ($this->cache->has($cacheTag, $cacheKey)){
            return $this->cache->get($cacheTag, $cacheKey);
        }
        if ($item = $obj::where($key, '=', $value)->first()){
            $this->cache->put($cacheTag, $cacheKey, $item);
            return $item;
        }
        return false;
    }

    /**
     * @inheritdoc
     */
    public function findMany($ids){
        $cacheTag = $this->makeCacheTag();
        $obj = $this->getModelName();
        $cacheKey = $this->cache->makeCacheKey($cacheTag, $ids);
        if ($this->cache->has($cacheTag, $cacheKey)){
            return $this->cache->get($cacheTag, $cacheKey);
        }
        if ($items = $obj::findMany($ids)){
            $this->cache->put($cacheTag, $cacheKey, $items);
            return $items;
        }
        return null;
    }

    /**
     * @inheritdoc
     */
    public function findManyIn($field, $values){
        $cacheTag = $this->makeCacheTag();
        $obj = $this->getModelName();
        $parts = $values;
        array_unshift($parts, $field);
        $cacheKey = $this->cache->makeCacheKey($cacheTag, $parts);
        if ($this->cache->has($cacheTag, $cacheKey)){
            return $this->cache->get($cacheTag, $cacheKey);
        }
        if ($items = $obj::whereIn($field, $values)->get()){
            $this->cache->put($cacheTag, $cacheKey, $items);
            return $items;
        }
        return null;
    }

    /**
     * @inheritdoc
     */
    public function getByConditions($conditions, $load = null){
        $cacheTag = $this->makeCacheTag();
        $obj = $this->getModelName();
        $parts = [];
        foreach ($conditions as $k => $v){
            $parts[]= $k;
            $parts[]= $v;
        }
        $cacheKey = $this->cache->makeCacheKey($cacheTag, $parts);
        if ($this->cache->has($cacheTag, $cacheKey)){
            return $this->cache->get($cacheTag, $cacheKey);
        }
        $where = join(' AND ', array_map(function($key){
            return "{$key} = ?";
        }, array_keys($conditions)));
        if ($item = $obj::whereRaw($where, array_values($conditions))->get()){
            if ($load) $item->load($load);
            $this->cache->put($cacheTag, $cacheKey, $item);
            return $item;
        }
        return null;
    }

    /**
     * @inheritdoc
     */
    public function findByConditions($conditions, $load = null){
        $cacheTag = $this->makeCacheTag();
        $obj = $this->getModelName();
        $parts = [];
        foreach ($conditions as $k => $v){
            $parts[]= $k;
            $parts[]= $v;
        }
        $cacheKey = $this->cache->makeCacheKey($cacheTag, $parts);
        if ($this->cache->has($cacheTag, $cacheKey)){
            return $this->cache->get($cacheTag, $cacheKey);
        }
        $where = join(' AND ', array_map(function($key){
            return "{$key} = ?";
        }, array_keys($conditions)));
        if ($item = $obj::whereRaw($where, array_values($conditions))->first()){
            if ($load) $item->load($load);
            $this->cache->put($cacheTag, $cacheKey, $item);
            return $item;
        }
        return null;
    }

    /**
     * @inheritdoc
     */
    public function getItems($limit = 10, $page = 0){
        $cacheTag = $this->makeCacheTag();
        $obj = $this->getModelName();
        $key = $this->cache->makeCacheKey($cacheTag, ['limit', $limit, 'page', $page]);
        if ($this->cache->has($cacheTag, $key)){
            return $this->cache->get($cacheTag, $key);
        }
        $items = $obj::paginate($limit);
        $this->cache->put($cacheTag, $key, $items);
        return $items;
    }

    /**
     * @inheritdoc
     */
    public function load($sort = null, $load = null, $limit = null, $page = 0){
        $cacheTag = $this->makeCacheTag();
        $obj = $this->getModelName();
        if (is_array($sort)){
            $parts = join('-', array_map(function($key) use ($sort){
                return "{$key}={$sort[$key]}";
            }, array_keys($sort)));
            $key = $this->cache->makeCacheKey($cacheTag, ['sort', $parts, 'limit', $limit, 'page', $page]);
        }
        else{
            $key = $this->cache->makeCacheKey($cacheTag, ['sort', $sort, 'limit', $limit, 'page', $page]);
        }
        if ($this->cache->has($cacheTag, $key)){
            return $this->cache->get($cacheTag, $key);
        }
        $q = $obj::query();
        if ($sort) {
            if (is_array($sort)){
                $q = $q->orderBy($sort['field'], $sort['type']);
            }
            else{
                $q = $q->orderBy($sort);
            }
        }
        $items = $limit ? $q->paginate($limit) : $q->get();
        if ($load) $items->load($load);
        $this->cache->put($cacheTag, $key, $items);
        return $items;
    }

    /**
     * @inheritdoc
     */
    public function count()
    {
        $cacheTag = $this->makeCacheTag();
        $obj = $this->getModelName();
        $key = $this->cache->makeCacheKey($cacheTag, $obj, ['count', 1]);
        if ($this->cache->has($cacheTag, $key)){
            return $this->cache->get($cacheTag, $key);
        }
        $count = $obj::count();
        $this->cache->put($cacheTag, $key, $count);
        return $count;
    }

}
