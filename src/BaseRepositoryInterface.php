<?php
/**
 * Created by PhpStorm.
 * User: keithwatanabe
 * Date: 10/8/15
 * Time: 2:41 PM
 */

namespace Jackhammer;


interface BaseRepositoryInterface
{
    /**
     * @return string
     */
    public function getTable();

    /**
     * @param int $id
     * @param array $load
     * @return mixed
     */
    public function find($id, $load = null);

    /**
     * @param $id
     * @param null $load
     * @return mixed
     */
    public function findAndLock($id, $load = null);

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
     * @return mixed
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

    public function get($limit, $page);
    /**
     * @param string $sort
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