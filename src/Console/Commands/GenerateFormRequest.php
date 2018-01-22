<?php
/**
 * Created by PhpStorm.
 * User: keithwatanabe
 * Date: 1/21/18
 * Time: 1:57 PM
 */

namespace Jackhammer\Console\Commands;

use Config;
use Illuminate\Console\Command;

/**
 * Class GenerateFormRequest
 * @package Jackhammer\Console\Commands
 */
class GenerateFormRequest extends Command
{
    use \Jackhammer\CoreTrait;

    /**
     * @var string
     */
    protected $signature = 'jackhammer:make-form-request {model}';

    /**
     * @var string
     */
    protected $description = 'Creates a form request using the model for rules';

    /**
     * GenerateFormRequest constructor.
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
        $model = $this->argument('model');
        if (!$this->doesModelExist($model)) {
            die("{$model} must be generated");
        }
        $request = $this->makeObjectName($model) . 'FormRequest';
        $view = view('jackhammer::form_request', [
            'header' => $this->header(),
            'namespace' => $this->makeNamespace('form_requests'),
            'model' => $model,
            'modelNamespace' => $this->makeModelNamespace(),
            'request' => $request,
        ]);
        $this->save($request, 'form_requests', $view);
    }

}