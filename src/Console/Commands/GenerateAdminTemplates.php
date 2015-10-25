<?php
/**
 * Created by PhpStorm.
 * User: keithwatanabe
 * Date: 10/21/15
 * Time: 3:11 PM
 */

namespace Conark\Jackhammer\Console\Commands;

use Config;
use DB;
use Log;
use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;

class GenerateAdminTemplates extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'jackhammer:admin-templates {model}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generates an admin templates from a model';

    /**
     * @var Illuminate\Filesystem\Filesystem
     */
    protected $files;

    /**
     * @var array
     */
    private $_columns;

    /**
     * @var Conark\Jackhammer\Models\BaseModel
     */
    private $_model;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(Filesystem $filesystem)
    {
        parent::__construct();
        $this->files = $filesystem;
    }

    private function _generateShowPage()
    {
        $table = str_plural($this->argument('model'));
        $stub = $this->files->get(__DIR__ . '/stubs/show.stub');
        $this->_replaceTokens(['type' => $table, 'resource' => $this->_getResource()], $stub);
        $this->_saveStub('show', $stub);
    }

    /**
     * @throws \Exception
     */
    private function _getModel()
    {
        if (!$this->_model){
            $model = str_singular(studly_case($this->argument('model')));
            if (!($modelPath = Config::get('jackhammer.models'))) throw new \Exception('jackhammer models not defined');
            $modelFile = 'App\\' . str_replace('/', '\\', "{$modelPath}/{$model}");
            $this->_model = new $modelFile();
        }
        return $this->_model;
    }

    private function _generateDetailsPage()
    {
        $obj = $this->_getModel();
        $fields = $obj->getFillable();
        $out = '';
        foreach ($fields as $f){
            $out .= "@include('helpers.data_field', ['field' => '{$f}', 'model' => \$model])\n";
        }
        $this->_saveStub('details', $out);
    }

    private function _generateIndexPage()
    {
        $table = str_plural($this->argument('model'));
        if (!($linkTextField = Config::get("jackhammer.{$table}.admin_templates.index.link_text_field"))) {
            $model = $this->_getModel();
            $fields = $model->getFillable();
            $bad = ['id', 'created_at', 'updated_at'];
            foreach ($fields as $f){
                if (in_array($f, $bad)) continue;
                else{
                    $linkTextField = $f;
                    break;
                }
            }
            //throw new \Exception("jackhammer.{$table}.admin_templates.index.link_text_field not defined");
        }
        $stub = $this->files->get(__DIR__ . '/stubs/index.stub');
        $this->_replaceTokens(['linkTextField' => $linkTextField, 'type' => $table], $stub);
        $this->_saveStub('index', $stub);
    }

    /**
     * @return string
     */
    private function _getResource()
    {
        return str_singular($this->argument('model'));
    }

    /**
     * @param string $filename
     * @param string $stub
     */
    private function _saveStub($filename, $stub)
    {
        $file = base_path("resources/views/{$this->_getResource()}/{$filename}.blade.php");
        if (!file_exists($file)){
            $this->files->put($file, $stub);
        }
    }

    /**
     * @param array $tokens
     * @param string $stub
     * @return string
     */
    private function _replaceTokens($tokens = [], &$stub)
    {
        foreach ($tokens as $token => $val){
            $stub = str_replace("<{$token}>", $val, $stub);
        }
    }

    private function _generateCreatePage()
    {
        $type = str_singular($this->argument('model'));
        $stub = $this->files->get(__DIR__ . '/stubs/create.stub');
        $this->_replaceTokens(['type' => $type], $stub);
        $this->_saveStub('create', $stub);
    }

    private function _generateEditPage()
    {
        $type = str_singular($this->argument('model'));
        $stub = $this->files->get(__DIR__ . '/stubs/edit.stub');
        $this->_replaceTokens(['type' => $type], $stub);
        $this->_saveStub('edit', $stub);
    }

    /**
     * @param string $model
     */
    private function _loadColumns($model)
    {
        $columns = DB::table('information_schema.columns')
            ->where('table_schema', '=', $model->getConnection()->getDatabaseName())
            ->where('table_name', '=', $model->getTable())
            ->get();
        foreach ($columns as $c){
            $this->_columns[$c->COLUMN_NAME] = $c;
        }
    }

    /**
     * @param array $col
     */
    private function _getColumnType($col)
    {
        $map = ['tinyint(1)' => 'bool'];
    }

    /**
     * @param string $f
     * @return string
     */
    private function _generateFormField($f)
    {
        // trollollollolllolll
        //<?php $selected = isset($product->brand->id) ? $product->brand->id : null
        // @include('helpers.form_select', ['name' => 'brand_id', 'items' => $brands, 'display' => 'name', 'selected' => $selected, 'options' => ['id' => 'id']])
        // there really should be a password database type
        $model = $this->_getModel();
        if ($info = Config::get("{$model->getTable()}.admin_templates.form.{$f}")){
            if ('select' == $info['type']){
                return "@include('helpers_form_select', ['name' => '{$f}', 'items' => \${$model->getTable()}, 'display' => '{$info['display']}', 'options' => ['id' => 'id']])";
            }

        }
        if (in_array($f, ['password', 'pwd', 'pass', 'passwd'])){
            return "@include('helpers.form_password', ['field' => '{$f}', 'model' => \$model])";
        }
        if (isset($this->_columns[$f])){
            switch ($this->_columns[$f]->COLUMN_TYPE){
                case 'tinyint(1)':
                    return "@include('helpers.form_checkbox', ['field' => '{$f}', 'model' => \$model])";
                case 'text':
                    return "@include('helpers.form_textarea', ['field' => '{$f}', 'model' => \$model])";
                default:
                    return "@include('helpers.form_text', ['field' => '{$f}', 'model' => \$model])";
            }
        }
        throw new \Exception("{$f} is not a column");
    }

    private function _generateFormPage()
    {
        $stub = $this->files->get(__DIR__ . '/stubs/form.stub');
        $obj = $this->_getModel();
        $fields = $obj->getFillable();
        $arr = [];
        foreach ($fields as $f){
            $arr[]= $this->_generateFormField($f);
        }
        $this->_replaceTokens(['replace_fields' => join("\n", $arr)], $stub);
        $this->_saveStub('form', $stub);
    }


    private function _generateResourceDirectory()
    {
        $resource = str_singular($this->argument('model'));
        $dir = base_path("resources/views/{$resource}");
        if (!file_exists($dir)){
            mkdir($dir);
            Log::info("created directory {$dir}");
        }
    }

    public function handle()
    {
        $this->_loadColumns($this->_getModel());
        $this->_generateResourceDirectory();
        $this->_generateIndexPage();
        $this->_generateShowPage();
        $this->_generateDetailsPage();
        $this->_generateCreatePage();
        $this->_generateEditPage();
        $this->_generateFormPage();
    }
}