<?php
/**
 * Created by PhpStorm.
 * User: keithwatanabe
 * Date: 10/13/15
 * Time: 4:46 PM
 */

namespace Jackhammer\Console\Commands;

use Illuminate\Console\Command;

/**
 * This is a super version of the laravel resource controller creator
 * as it not only generates the stub methods but fills in blanks
 *
 * Class GenerateController
 * @package Conark\Jackhammer\Console\Commands
 */
class GenerateRestController extends Command {
    use \Jackhammer\CoreTrait;

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
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $model = $this->makeObjectName($this->argument('model'));
        if (!$this->doesModelExist($model)) {
            die("{$model} has not been created");
        }
        if (!$this->doesFormRequestExist($model)) {
            die("{$this->getFormRequestFile($model)} has not been created");
        }
        if (!$this->doesRepositoryExist($model)) {
            die("{$this->getRepositoryInterfaceFile($model)} has not been created");
        }
        if (!$this->doesTransformerExist($model)) {
            die("{$this->getTransformerFile($model)} has not been created");
        }
        $arr = [
            'header' => $this->header(),
            'namespace' => $this->makeRestControllerNamespace(),
            'className' => $this->makeClassname($model, 'controller'),
            'formRequestNamespace' => $this->makeFormRequestNamespace(),
            'repositoryNamespace' => $this->makeRepositoryContractNamespace(),
            'repositoryInterface' => $this->makeRepositoryName($model),
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