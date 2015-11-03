<?php
/**
 * Created by PhpStorm.
 * User: keithwatanabe
 * Date: 10/13/15
 * Time: 4:46 PM
 */

namespace Conark\Jackhammer\Console\Commands;

use Config;
use Conark\Jackhammer\CoreTrait;
use Illuminate\Console\Command;

/**
 * This is a super version of the laravel resource controller creator
 * as it not only generates the stub methods but fills in blanks
 *
 * Class GenerateController
 * @package Conark\Jackhammer\Console\Commands
 */
class GenerateController extends Command {
    use CoreTrait;

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
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $model = studly_case($this->argument('model'));
        $this->checkFile($this->getModelFile($model));
        $this->checkFile($this->getRepositoryFile($model));
        $arr = [
            'header' => $this->header(),
            'namespace' => $this->_createNamespace(),
            'className' => $this->makeClassname($model, 'controller'),
            'repositoryNamespace' => $this->makeRepositoryNamespace(),
            'repositoryInterface' => $this->makeUseRepositoryInterface($model),
            'repositoryInterfaceVar' => lcfirst($model) . 'RepositoryInterface',
            'model' => $model,
            'transformPath' => $this->makeTransformerNamespace(),
        ];
        if ($this->hasPolicy($model)){
            $arr['policyPath'] = $this->makePolicyNamespace();
            $arr['policy'] = $this->makeClassname($model, 'policy');
        }
        $view = view('jackhammer::rest_controller', $arr);
        $this->save("{$model}Controller", 'rest_controllers', $view);
    }
}