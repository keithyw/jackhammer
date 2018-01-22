<?php
/**
 * Created by PhpStorm.
 * User: keithwatanabe
 * Date: 10/8/15
 * Time: 2:41 PM
 */

namespace Jackhammer\Contracts;

/**
 * Interface BaseRepository
 * @package App\Repositories
 */
interface BaseRepository
{
    /**
     * @return string
     */
    public function getTable();

    /**
     * @return BaseModel
     */
    public function getModel();

    /**
     * @param int $id
     * @param array $load
     * @return mixed
     */
    public function find($id, $load = null);

    /**
     * @param $id
     * @param array $load
     * @return mixed
     */
    public function findAndLock($id, $load = null);

    /**
     * Finds by a variety of key/value conditions (where each key/value will be connected via an AND clause).
     * Only grabs the first item, assuming that the item can be identified.
     *
     * @param array $conditions
     * @param array $load
     * @return mixed
     */
    public function findByConditions($conditions, $load = null);

    /**
     * @param $conditions
     * @param array $load
     * @return mixed
     */
    public function getByConditions($conditions, $load = null);

    /**
     * @param string $key
     * @param mixed $value
     * @return bool|mixed
     */
    public function findWhere($key, $value);

    /**
     * @param array $ids
     * @return mixed
     */
    public function findMany($ids);

    /**
     * @param string $field
     * @param array $values
     * @return mixed
     */
    public function findManyIn($field, $values);

    /**
     * @param int $id
     * @return boolean
     */
    public function delete($id);

    /**
     * @param array $data
     * @return mixed
     */
    public function create(array $data);

    /**
     * @param int $id
     * @param array $data
     * @return mixed
     */
    public function update($id, array $data);

    /**
     * @param int $limit
     * @param int $page
     * @return mixed
     */
    public function getItems($limit = 10, $page = 0);

    /**
     * @param string|array $sort
     * @param array $load
     * @param int $limit
     * @param int $page
     * @return mixed
     */
    public function load($sort = 'id', $load = null, $limit = null, $page = null);

    /**
     * @return int
     */
    public function count();
}