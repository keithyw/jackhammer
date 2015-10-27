<?php
/**
 * Created by PhpStorm.
 * User: keithwatanabe
 * Date: 10/20/15
 * Time: 1:36 PM
 */

namespace Conark\Jackhammer\Console\Commands;

use Config;
use Conark\Jackhammer\CoreTrait;
use Illuminate\Console\Command;
use Log;

/**
 * This is a super version of the laravel admin resource controller creator
 * as it not only generates the stub methods but fills in blanks
 *
 * Class GenerateController
 * @package Conark\Jackhammer\Console\Commands
 */
class GenerateAdminController extends Command {
    use CoreTrait;

    /**
     * @var BaseModel
     */
    protected $_model;

    private $_header = '<?php';

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'jackhammer:admin-controller {model}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generates an admin controller from a model';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }


    /**
     * @return string
     */
    private function _createNamespace()
    {
        if (!($restDir = Config::get('jackhammer.admin_controllers'))) throw new \Exception('jackhammer admin_controllers not defined');
        return 'App\\' . str_replace('/', '\\', $restDir);
    }

    /**
     * @param string $model
     * @return string
     */
    private function _createClassname($model)
    {
        return "{$model}Controller";
    }

    /**
     * @param string $path
     * @return string
     */
    private function _createRepositoryNamespace($path)
    {
        return 'App\\' . "{$path}";
    }

    /**
     * @param string $inf
     * @return string
     */
    private function _makeConstructorParameters($inf)
    {
        $inf .= "RepositoryInterface";
        $var = $this->makeVariableName($inf);
        return "{$inf} {$var}";
    }

    /**
     * needs to follow the format
     * protected $<repositoryName>;
     *
     * @param string $r
     * @return string
     */
    private function _makeProtectedRepository($r)
    {
        return 'protected ' . $this->makeVariableName($r) . "Repository;\n";
    }

    /**
     * $this-><protected member> = $<repositoryInterface>;
     *
     * @param string $r
     * @return string
     */
    private function _makeAssignment($r)
    {
        return '$this->' .  str_replace('$', '', $this->makeVariableName($r)) . 'Repository = ' . $this->makeVariableName($r) . "RepositoryInterface;\n";
    }
    /**
     * Need to generate the following:
     * 1) all interfaces for use statement
     * 2) all protected data members
     * 3) constructor injection statement
     * 4) constructor interface data member assignment
     *
     */
    private function _getExternalRepositoryInfo()
    {

        $str = "jackhammer.{$this->_model[$this->argument('model')]->getTable()}.admin_controller.repositories";
        $interfaces = [];
        $constructorParams = [];
        $dataMembers = [];
        $assignments = [];
        Log::info("str {$str}");
        if ($repositories = Config::get($str)){
            foreach ($repositories as $r){
                $interfaces[]= $this->makeUseRepositoryInterface($r);
                $dataMembers[]= $this->_makeProtectedRepository($r);
                $constructorParams[]= $this->_makeConstructorParameters($this->makeObjectName($r));
                $assignments[]= $this->_makeAssignment($r);
            }
        }
        return [
            'params' => count($constructorParams) > 0 ? ', ' . join(', ', $constructorParams) : '',
            'interfaces' => $interfaces,
            'dataMembers' => $dataMembers,
            'assignments' => $assignments
        ];
    }
    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->loadModel();
        $model = studly_case(str_singular($this->argument('model')));
        if (!($modelPath = Config::get('jackhammer.models'))) throw new \Exception('jackhammer models not defined');
        if (!($repositoryPath = Config::get('jackhammer.repositories'))) throw new \Exception('jackhammer repositories not defined');
        $modelDir = app_path() . '/' . $modelPath;
        $repositoryDir = app_path() . '/' . $repositoryPath;
        $modelFile = "{$modelDir}/{$model}.php";
        $repositoryFile = "{$repositoryDir}/{$model}Repository.php";
        if (!file_exists($modelFile)) throw new \Exception("{$modelFile} does not exist");
        if (!file_exists($repositoryFile)) throw new \Exception("{$repositoryFile} does not exist");
        $view = view('jackhammer::admin_controller', [
            'header' => $this->_header,
            'namespace' => $this->_createNamespace(),
            'className' => $this->_createClassname($model),
            'repositoryNamespace' => $this->_createRepositoryNamespace($repositoryPath),
            'repositoryInterface' => "{$model}RepositoryInterface",
            'repositoryInterfaceVar' => lcfirst($model) . 'RepositoryInterface',
            'model' => $model,
            'modelPath' => "App\\{$modelPath}",
            'repositoryInfo' => $this->_getExternalRepositoryInfo()
        ]);
        $dir = app_path() . '/' . Config::get('jackhammer.admin_controllers');
        if (!file_exists($dir)){
            mkdir($dir, 0700, true);
        }
        $controllerFile = app_path() . '/' . Config::get('jackhammer.admin_controllers') . "/{$model}Controller.php";
        if (!file_exists($controllerFile)){
            file_put_contents($controllerFile, $view);
        }
    }
}