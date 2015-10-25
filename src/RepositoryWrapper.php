<?php
/**
 * Created by PhpStorm.
 * User: keithwatanabe
 * Date: 5/20/15
 * Time: 3:12 PM
 */

namespace Conark\Jackhammer;


/**
 * Convenience class that wraps the BaseRepository
 * class and some of the key functions
 *
 * Class RepositoryWrapper
 * @package Conark\Repositories
 */
class RepositoryWrapper implements BaseRepositoryInterface{
    /**
     * @var string
     */
    protected $_model;
    protected $_base;

    public function __construct($model){
        $this->_base = new BaseRepository();
        $this->_model = $model;
    }

    public function getTable(){
        return $this->_model->getTable();
    }

    public function makeCacheKey($key, $parts){
        return $this->_base->makeCacheKey($key, $parts);
    }
    /**
     * @param int $id
     * @param array $load
     * @return mixed
     */
    public function find($id, $load = null)
    {
        return $this->_base->find($id, $this->_model->getTable(), get_class($this->_model), $load);
    }

    /**
     * @param $id
     * @param null $load
     * @return mixed
     */
    public function findAndLock($id, $load = null)
    {
        return $this->_base->findAndLock($id, $this->_model->getTable(), get_class($this->_model), $load);
    }


    /**
     * @param string $key
     * @param mixed $value
     * @return bool|mixed
     */
    public function findWhere($key, $value){
        return $this->_base->findWhere($key, $value, $this->_model->getTable(), get_class($this->_model));
    }

    /**
     * @param array $ids
     * @return mixed
     */
    public function findMany($ids){
        return $this->_base->findMany($ids, $this->_model->getTable(), get_class($this->_model));
    }

    /**
     * @param string $field
     * @param array $values
     * @return mixed
     */
    public function findManyIn($field, $values){
        return $this->_base->findManyIn($field, $values, $this->_model->getTable(), get_class($this->_model));
    }

    /**
     * @param int $id
     * @return mixed
     */
    public function delete($id)
    {
        return $this->_base->delete($id, $this->_model->getTable(), get_class($this->_model));
    }

    /**
     * @param array $data
     * @return mixed
     */
    public function create(array $data)
    {
        return $this->_base->create($data, $this->_model->getTable(), get_class($this->_model));
    }

    /**
     * @param int $id
     * @param array $data
     * @return mixed
     */
    public function update($id, array $data)
    {
        return $this->_base->update($id, $data, $this->_model->getTable(), get_class($this->_model));
    }

    /**
     * @param int $limit
     * @param int $page
     * @return mixed
     */
    public function get($limit = 10, $page = 0){
        return $this->_base->getItems($this->_model->getTable(), get_class($this->_model), $limit, $page);
    }

    /**
     * @param string $sort
     * @param array $load
     * @param int $limit
     * @param int $page
     */
    public function load($sort = 'id', $load = null, $limit = null, $page = 0){
        return $this->_base->load($this->_model->getTable(), get_class($this->_model), $sort, $load, $limit, $page);
    }

    /**
     * @return int
     */
    public function count()
    {
        return $this->_base->count($this->_model->getTable(), get_class($this->_model));
    }


}