<?php
/**
 * Created by PhpStorm.
 * User: keithwatanabe
 * Date: 10/19/15
 * Time: 12:41 PM
 */

namespace Jackhammer\Console\Commands;

use Config;
use Illuminate\Console\Command;

class GenerateTransformer extends Command
{
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
     * @param string $model
     * @return string
     */
    private function _createClassname($model){
        return "{$model}Transformer";
    }

    public function handle()
    {
        $model = studly_case($this->argument('model'));
        if (!($transformerBase = Config::get('jackhammer.transformers'))) throw new \Exception("jackhammer.transformers not defined");
        if (!($modelBase = Config::get('jackhammer.models'))) throw new \Exception("jackhammer.models not defined");
        $namespace = "App\\{$transformerBase}";
        $rvd = "App\\{$modelBase}\\{$model}";
        $obj = new $rvd();
        $attributes = $obj->getFillable();
        $view = view('jackhammer::transformer', [
            'header' => $this->_header,
            'namespace' => $namespace,
            'classname' => $this->_createClassname($model),
            'model' => $model,
            'modelPath' => "App\\{$modelBase}",
            'attributes' => $attributes
        ]);
        $dir = app_path() . '/' . Config::get('jackhammer.transformers');
        if (!file_exists($dir)){
            mkdir($dir, 0700, true);
        }
        $transformerFile = app_path() . '/' . Config::get('jackhammer.transformers') . "/{$model}Transformer.php";
        if (!file_exists($transformerFile)){
            file_put_contents($transformerFile, $view);
        }
    }
}