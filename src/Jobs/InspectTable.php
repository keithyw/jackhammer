<?php

namespace Conark\Jackhammer\Jobs;

use Conark\Jackhammer\CoreTrait;
use Config;
use DB;
use Illuminate\Contracts\Bus\SelfHandling;
use Log;

class InspectTable extends Job implements SelfHandling
{

    use CoreTrait;

    /**
     * @var string
     */
    private $_database;

    private $_header = '<?php';

    /**
     * @var BaseModel
     */
    protected $_model;

    /**
     * @var array
     */
    private static $selects = ['column_name as Field',
        'column_type as Type',
        'is_nullable as Null',
        'column_key as Key',
        'column_default as Default',
        'extra as Extra', 'data_type as Data_Type'];

    /**
     * Name of the table to inspect
     *
     * @var string
     */
    private $_table;


    /**
     * @param string $table
     * @param string $database
     */
    public function __construct($table, $database = null)
    {
        $this->_table = $table;
        $this->_model = $table;
        $this->_database = $database ? $database : env('DB_DATABASE');
    }

    /**
     * We need to extract the table and the model
     *
     * @param string $name
     * @param string $path
     * @return mixed
     */
    private function _getRelationsInfo($name, $path){
        $namespace = "App\\{$path}";
        $id = "{$this->_database}/{$name}";
        $relation = DB::table('information_schema.innodb_sys_foreign')
            ->where('id', '=', $id)
            ->first();
        $str = str_replace("{$this->_database}/", '', $relation->REF_NAME);

        return [
            'method' => camel_case(str_singular($str)),
            'belongsTo' => "{$namespace}\\" . studly_case(str_singular($str))
        ];
    }

    /**
     * @param string $modelPath
     */
    private function _getReferences($modelPath){
        $refs = DB::table('information_schema.innodb_sys_foreign')
            ->where('ref_name', '=', "{$this->_database}/{$this->_table}")
            ->get();
        $namespace = "App\\{$modelPath}";
        $arr = [];
        foreach ($refs as $ref){
            $name = studly_case(str_replace("{$this->_database}/", '', $ref->FOR_NAME));
            $t = "jackhammer.{$this->_table}." . str_replace("{$this->_database}/", '', $ref->FOR_NAME) . '.hasType';
            $hasType = Config::get($t);
            $name = $hasType == 'one' ? str_singular($name) : $name;
            $method = camel_case($name);
            $arr[]= [
                'method' => $method,
                'model' => "{$namespace}\\" . studly_case(str_singular($name)),
                'hasType' => $hasType
            ];
        }
        return $arr;
    }
    /**
     * @param array $constraints
     * @param string $path
     * @return array
     */
    private function _parseConstraints(array $constraints, $path){
        $arr = [];
        foreach ($constraints as $c){
            $arr[]= $this->_getRelationsInfo($c->CONSTRAINT_NAME, $path);
        }
        return $arr;
    }

    /**
     */
    private function _generateRepository(){
        $repository = $this->makeRepositoryName($this->_table);
        $interface = $this->makeUseRepositoryInterface($this->_table);
        $view = view('jackhammer::repository_interface', ['interface' => $interface, 'header' => $this->_header]);
        if (!($repositoriesPath = Config::get('jackhammer.repositories'))) throw new \Exception('jackhammer repositories not defined');
        $path = app_path() . '/' . $repositoriesPath;
        if (!file_exists($path)) {
            mkdir($path);
            Log::info("path {$path} has been created");
        }
        $interfaceFile = "{$path}/{$interface}.php";
        file_put_contents($interfaceFile, $view);

        if (!($modelPath = Config::get('jackhammer.models'))) throw new \Exception('jackhammer models not defined');
        //$path = app_path() . '/' . $modelPath;
        $model = $this->makeObjectName($this->_table);

        $repositoryView = view('jackhammer::repository',
            ['header' => $this->_header,
            'model' => $model,
            'modelPath' => $modelPath,
            'interface' => $interface,
            'className' => $repository]);
        $repositoryFile = "{$path}/{$repository}.php";
        file_put_contents($repositoryFile, $repositoryView);

    }

    /**
     * Generate the rules section using some db info,
     * some configuration
     *
     * @param array $table
     * @param array $constraints
     * @param array $hidden
     * @return array
     */
    private function _generateValidationRules(array $table, array $constraints, array $hidden)
    {
        $fields = [];
        foreach ($table as $col) {
            if (in_array($col->Field, $hidden)) {
                continue;
            }
            $rules = [];
            if ('No' == $col->Null) {
                $rules[] = 'Required';
            }
            if (in_array($col->Data_Type, ['tinyint', 'int'])) {
                if ('tinyint' == $col->Data_Type && 'tinyint(1)' == $col->Type) {
                    $rules[] = 'Boolean';
                } else {
                    $rules[] = 'Integer';
                }
            }
            if ('UNI' == $col->Type) {
                $rules[] = "unique:{$this->_table}";
            }
            if (preg_match('/varchar\((\d+)\)/', $col->Type, $matches)) {
                $rules[] = "Max:{$matches[1]}";
            }
            // use conventions for certain validations
            if (in_array($col->Field, ['email', 'email_addr', 'email_address'])) {
                $rules[] = "Email";
            }
            if (in_array($col->Field, ['zipcode', 'postal', 'postal_code', 'zip'])) {
                $rules[] = 'regex:/^[0-9]{5}(?:-[0-9]{4})?$/';
            }
            $fields[$col->Field] = join('|', array_merge($rules, $this->getRulesForColumn($this->_table, $col->Field)));
        }
        return $fields;
    }

    /**
     * @param array $table
     * @param array $constraints
     */
    private function _generateModel(array $table, array $constraints)
    {
        $hidden = $this->getHiddenFields($this->_table);
        $columns = array_unique(array_filter(array_map(function($col) use($hidden){
            if (in_array($col->Field, $hidden)) return false;
            return "'{$col->Field}'";
        }, $table)));
        if (!($modelPath = Config::get('jackhammer.models'))) throw new \Exception('jackhammer models not defined');
        $path = app_path() . '/' . $modelPath;
        $relations = $this->_parseConstraints($constraints, $modelPath);
        $references = $this->_getReferences($modelPath);
        $filteredHidden = array_map(function($col) use($hidden){
            return sprintf("'%s'", $col);
        }, $hidden);
        $view = view('jackhammer::model', ['table' => $table,
            'tableName' => $this->_table,
            'fillable' => $columns,
            'header' => $this->_header,
            'relations' => $relations,
            'references' => $references,
            'rules' => $this->_generateValidationRules($table, $constraints, $hidden),
            'hidden' => $filteredHidden]);
        if (!file_exists($path)) {
            mkdir($path);
            Log::info("path {$path} has been created");
        }
        $file = "{$path}/{$this->makeObjectName($this->_table)}.php";
        //if (!file_exists($file)){
            file_put_contents($file, $view);
            Log::info("{$file} model has been created");
        //}
        $this->_generateRepository();
    }

    private function _generateServiceProvider()
    {
        // move to data member ?
        $serviceProviderName = Config::get('jackhammer.repositoryServiceProvider');
        $provider = app_path() . "/Providers/{$serviceProviderName}.php";
        $view = view('jackhammer::provider', ['header' => $this->_header]);
        if (!file_exists($provider)){
            file_put_contents($provider, $view);
            Log::info("Provider {$provider} has been created");
        }


    }

    /**
     *
     * select * from
    INFORMATION_SCHEMA.TABLE_CONSTRAINTS
    where table_schema = 'homestead'
    and constraint_type = 'FOREIGN KEY';
     *
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $this->_generateServiceProvider();
        $table = DB::table('information_schema.columns')
            ->where('table_schema', '=', $this->_database)
            ->where('table_name', '=', $this->_table)
            ->get(self::$selects);

        $constraints = DB::table('information_schema.table_constraints')
            ->where('table_schema', '=', $this->_database)
            ->where('table_name', '=', $this->_table)
            ->where('constraint_type', '=', 'FOREIGN KEY')
            ->get();

        $this->_generateModel($table, $constraints);

    }
}
