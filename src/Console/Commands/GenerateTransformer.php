<?php
/**
 * Created by PhpStorm.
 * User: keithwatanabe
 * Date: 10/19/15
 * Time: 12:41 PM
 */

namespace Jackhammer\Console\Commands;

use Illuminate\Console\Command;

/**
 * Class GenerateTransformer
 * @package Jackhammer\Console\Commands
 */
class GenerateTransformer extends Command
{
    use \Jackhammer\CoreTrait;

    private $_header = '<?php';

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'jackhammer:transformer {model}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generates a transformer from a model';

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
     *
     */
    public function handle()
    {
        $model = $this->makeObjectName($this->argument('model'));
        if (!$this->doesModelExist($model)) {
            die("{$model} has not been created");
        }
        $modelBase = $this->makeModelNamespace();
        $rvd = "{$modelBase}\\{$model}";
        $obj = new $rvd();
        $attributes = $obj->getFillable();
        $classname = $this->makeClassname($model, 'transformer');
        $view = view('jackhammer::transformer', [
            'header' => $this->header(),
            'namespace' => $this->makeTransformerNamespace(),
            'classname' => $classname,
            'model' => $model,
            'modelPath' => $modelBase,
            'attributes' => $attributes,
        ]);
        $this->save($classname, 'transformers', $view);
    }
}