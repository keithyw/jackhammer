<?php

namespace Conark\Jackhammer\Jobs;

use Config;
use DB;
use Log;
//use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Bus\SelfHandling;

class InspectTable extends Job implements SelfHandling
{
    /**
     * @var string
     */
    private $_database;

    private $_header = '<?php';

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
     * @param string $table
     */
    private function _generateRepository($table){
        $repository = studly_case(str_singular($this->_table) . 'Repository');
        $interface = $repository . 'Interface';
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
        $model = studly_case(str_singular($this->_table));

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
     * @param string $table
     * @param array $constraints
     */
    private function _generateModel($table, array $constraints){
        $hidden = Config::get("jackhammer.{$this->_table}.hidden");
        $hidden = is_array($hidden) ? $hidden : [];
        array_push($hidden, 'id', 'created_at', 'updated_at');
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
            'hidden' => $filteredHidden]);
        if (!file_exists($path)) {
            mkdir($path);
            Log::info("path {$path} has been created");
        }
        $filename = studly_case(str_singular($this->_table));
        $file = "{$path}/{$filename}.php";
        //if (!file_exists($file)){
            file_put_contents($file, $view);
            Log::info("{$file} model has been created");
        //}
        $this->_generateRepository($table);
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

    //$this->callSilent('make:listener', ['name' => $listener, '--event' => $event]);

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
