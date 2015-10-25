<?php
/**
 * Created by PhpStorm.
 * User: keithwatanabe
 * Date: 10/13/15
 * Time: 4:46 PM
 */

namespace Conark\Jackhammer\Console\Commands;

use Config;
use Illuminate\Console\Command;

/**
 * This is a super version of the laravel resource controller creator
 * as it not only generates the stub methods but fills in blanks
 *
 * Class GenerateController
 * @package Conark\Jackhammer\Console\Commands
 */
class GenerateController extends Command {
    private $_header = '<?php';

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'jackhammer:rest-controller {model}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generates a REST controller from a model';

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
    private function _createNamespace(){
        //rest_controllers
        if (!($restDir = Config::get('jackhammer.rest_controllers'))) throw new \Exception('jackhammer rest_controllers not defined');
        return 'App\\' . str_replace('/', '\\', $restDir);
    }

    /**
     * @param string $model
     * @return string
     */
    private function _createClassname($model){
        return "{$model}Controller";
    }

    /**
     * @param string $path
     * @return string
     */
    private function _createRepositoryNamespace($path){
        return 'App\\' . "{$path}";
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $model = studly_case($this->argument('model'));
        if (!($modelPath = Config::get('jackhammer.models'))) throw new \Exception('jackhammer models not defined');
        if (!($repositoryPath = Config::get('jackhammer.repositories'))) throw new \Exception('jackhammer repositories not defined');
        if (!($transformerPath = Config::get('jackhammer.transformers'))) throw new \Exception('jackhammer transformers not defined');
        $modelDir = app_path() . '/' . $modelPath;
        $repositoryDir = app_path() . '/' . $repositoryPath;
        $modelFile = "{$modelDir}/{$model}.php";
        $repositoryFile = "{$repositoryDir}/{$model}Repository.php";
        if (!file_exists($modelFile)) throw new \Exception("{$modelFile} does not exist");
        if (!file_exists($repositoryFile)) throw new \Exception("{$repositoryFile} does not exist");
        $view = view('jackhammer::rest_controller', [
            'header' => $this->_header,
            'namespace' => $this->_createNamespace(),
            'className' => $this->_createClassname($model),
            'repositoryNamespace' => $this->_createRepositoryNamespace($repositoryPath),
            'repositoryInterface' => "{$model}RepositoryInterface",
            'repositoryInterfaceVar' => lcfirst($model) . 'RepositoryInterface',
            'model' => $model,
            'transformPath' => "App\\{$transformerPath}",
        ]);
        $dir = app_path() . '/' . Config::get('jackhammer.rest_controllers');
        if (!file_exists($dir)){
            mkdir($dir, 0700, true);
        }
        $controllerFile = app_path() . '/' . Config::get('jackhammer.rest_controllers') . "/{$model}Controller.php";
        if (!file_exists($controllerFile)){
            file_put_contents($controllerFile, $view);
        }
    }
}