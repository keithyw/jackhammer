<?php
/**
 * Created by PhpStorm.
 * User: keithwatanabe
 * Date: 10/20/15
 * Time: 10:41 AM
 */

namespace Conark\Jackhammer\Http\Controllers;

use App\Http\Controllers\Controller;
use Conark\Jackhammer\CoreTrait;
use Illuminate\Http\Request as MyRequest;
use Config;
use Request;

/**
 * Class BaseCoreResourceController
 * @package Conark\Jackhammer\Http\Controllers
 */
abstract class BaseCoreResourceController extends Controller
{
    use CoreTrait;
/**
 * This will be injected by the concrete controller's constructor
 *
 * @var Conark\Jackhammer\BaseRepositoryInterface
 */
    protected $repository;

    /**
     * @var BaseModel
     */
    protected $model;

    /**
     * Maybe null
     */
    protected $policy;

    /**
     * This will retrieve a new instance of the inherited controller's model
     *
     * @return Illuminate\Database\Eloquent\Model
     */
    abstract protected function getModel();

    /**
     * returns the prefix of where we're going to retrieve the templates
     *
     * could try intelligently parsing it but i like having flexibility
     *
     * @return string
     */
    abstract protected function getResourceDirectory();

    /**
     * returns the inherited controller's route
     *
     * @return string
     */
    abstract protected function getBaseRoute();

    /**
     * @return string
     */
    protected function getShowView()
    {
        return "{$this->getResourceDirectory()}/show";
    }

    /**
     * @return string
     */

    protected function getIndexView()
    {
        return "{$this->getResourceDirectory()}/index";
    }

    /**
     * @return string
     */
    protected function getCreateView()
    {
        return "{$this->getResourceDirectory()}/create";
    }

    /**
     * @return string
     */
    protected function getEditView()
    {
        return "{$this->getResourceDirectory()}/edit";
    }

    /**
     * @return int
     */
    public function limit()
    {
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
     * Loads up the lookup table information for create/edit
     * pages to load into drop downs
     */
    protected function lookup()
    {
        $str = "jackhammer.{$this->getModel()->getTable()}.admin_controller.repositories";
        $arr = [];
        if ($repositories = Config::get($str)){
            foreach ($repositories as $r){
                $repo = camel_case(str_singular($r)) . 'Repository';
                $arr[$r] = $this->$repo->load();
            }
        }
        return $arr;
    }

    /**
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        $arr = $this->lookup();
        $arr['model'] = $this->getModel();
        return view($this->getCreateView(), $arr);
    }

    /**
     * @param int $id
     * @return null
     */
    public function destroy($id)
    {
        if ($item = $this->repository->find($id)){
            if ($this->repository->delete($id)){
                return redirect()->route("{$this->getBaseRoute()}.index");
            }
        }
        abort(404);
    }

    /**
     * @param int $id
     * @return \Illuminate\View\View
     */
    public function edit($id)
    {
        if ($model = $this->repository->find($id)){
            $arr = $this->lookup();
            $arr['model'] = $model;
            return view($this->getEditView(), $arr);
        }
        abort(404);
    }

    /**
     * @return \Illuminate\View\View
     */
    public function index()
    {
        if ($items = $this->repository->load('id', [], $this->limit(), $this->page())){
            return view($this->getIndexView(), ['items' => $items]);
        }
        abort(404);
    }

    /**
     * @param int $id
     * @return \Illuminate\View\View
     */
    public function show($id)
    {
        if ($model = $this->repository->find($id)){
            return view($this->getShowView(), ['model' => $model]);
        }
        abort(404);
    }

    /**
     * We have to figure out how to get a Request that contains
     * all the validation rather than passing in a plain Request object
     *
     * @param MyRequest $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(MyRequest $request)
    {
        $model = $this->repository->create($request->all());
        if (isset($model->id)){
            return redirect()->route("{$this->getBaseRoute()}.show", ['id' => $model->id]);
        }
        // lame but need to figure out how to deal with errors
        // return redirect()->route('')
        return redirect()->route("{$this->getBaseRoute()}.create");
    }

    /**
     * Modify this part to incorporate policies
     *
     * Perhaps check to see if the controller has a policy associated with it.
     *
     * @param MyRequest $request
     * @param int $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(MyRequest $request, $id)
    {
        if ($this->repository->find($id)){
            $model = $this->repository->update($id, $request->all());
            return redirect()->route("{$this->getBaseRoute()}.show", ['id' => $id]);
        }
        abort(404);
    }

}