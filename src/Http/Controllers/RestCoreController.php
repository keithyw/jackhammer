<?php
/**
 * Created by PhpStorm.
 * User: keithwatanabe
 * Date: 10/13/15
 * Time: 11:45 AM
 */

namespace Conark\Jackhammer\Http\Controllers;

use App\Http\Controllers\Controller;
//use Illuminate\Http\Request;
use Config;
use League\Fractal\Pagination\IlluminatePaginatorAdapter;
use League\Fractal\Resource\Collection as FractalCollection;
use League\Fractal\Resource\Item;
use League\Fractal\Serializer\JsonApiSerializer;
use Request;

//use League\Fractal\Manager;

abstract class RestCoreController extends Controller{

    /**
     * @var League\Fractal\Manager
     */
    protected $manager;

    /**
     * This will be injected by the concrete controller's constructor
     *
     * @var Conark\Jackhammer\BaseRepositoryInterface
     */
    protected $repository;

    /**
     * Inherited controller will provide the concrete transformer
     * specific to the class.
     *
     * @return League\Fractal\TransformerAbstract
     */
    abstract protected function getTransformer();

    /**
     * @return int
     */
    public function limit(){
        return Request::has('limit') ? Request::input('limit') : Config::get('jackhammer.default_limit');
    }

    /**
     * @return int
     */
    public function page()
    {
        return Request::has('page') ? Request::get('page') : 1;
    }

    /**
     * @param Request $request
     * @return mixed
     */
    public function create(Request $request){

        return $this->repository->create($request->all());
    }

    /**
     * @param int $id
     * @return mixed
     */
    public function destroy($id)
    {
        if ($item = $this->repository->find($id)){
            if ($this->repository->delete($id)){
                return $item;
            }
        }
        return null;
    }

    /**
     * @param int $id
     * @return mixed
     */
    public function edit($id)
    {
        return $this->show($id);
    }

    /**
     * Simple/ignorant grab of everything
     *
     * @return mixed
     */
    public function index()
    {
        if ($items = $this->repository->load('id', [], $this->limit(), $this->page())){
            $resource = new FractalCollection($items, $this->getTransformer(), $this->repository->getTable());
            $adapter = new IlluminatePaginatorAdapter($items);
            $resource->setPaginator($adapter);
            return $this->manager->createData($resource)->toJson();
        }
        return null;

    }

    /**
     * @param int $id
     * @return mixed
     */
    public function show($id)
    {
        if ($item = $this->repository->find($id)){
            $resource = new Item($item, $this->getTransformer(), $item->getTable());
            return $this->manager->createData($resource)->toJson();
        }
        return null;
    }

    /**
     * Just passes everything into the update. If there's a stricter
     * requirement,
     *
     * @param Request $request
     * @param $id
     * @return mixed
     */
    public function update(Request $request, $id){
        if ($this->repository->find($id)){
            return $this->repository->update($id, $request->all());
        }
        return null;
    }
}