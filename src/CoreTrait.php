<?php
/**
 * Created by PhpStorm.
 * User: keithwatanabe
 * Date: 10/23/15
 * Time: 11:53 AM
 */

namespace Conark\Jackhammer;

use Config;

trait CoreTrait {

    /**
     * There's got to be a better way to do this....
     *
     * @return string
     */
    public function header()
    {
        return '<?php';
    }
    /**
     * @throws \Exception
     */
    public function loadModel()
    {
        if (!isset($this->_model[$this->argument('model')])){
            $model = $this->makeObjectName($this->argument('model'));
            if (!($modelPath = Config::get('jackhammer.models'))) throw new \Exception('jackhammer models not defined');
            $modelFile = 'App\\' . str_replace('/', '\\', "{$modelPath}/{$model}");
            $this->_model[$this->argument('model')] = new $modelFile();
        }
        return $this->_model[$this->argument('model')];
    }

    /**
     * @param string $type
     * @return string
     */
    public function makeNamespace($type)
    {
        if (!($part = Config::get("jackhammer.{$type}"))) throw new \Exception("jackhammer.{$type} not defined");
        return "App\\{$part}";
    }

    /**
     * @param string $name
     * @return string
     */
    public function makeVariableName($name)
    {
        return '$' . camel_case(str_singular($name));
    }

    /**
     * @param string $name
     * @return string
     */
    public function makeObjectName($name)
    {
        return str_singular(studly_case($name));
    }

    /**
     * @param string $name
     * @param string $type
     * @return string
     */
    public function makeClassname($name, $type)
    {
        return $this->makeObjectName($name) . studly_case($type);
    }

    /**
     * @param string $name
     * @return string
     */
    public function makeRepositoryName($name)
    {
        return "{$this->makeObjectName($name)}Repository";
    }
    /**
     * converts the repo (table name) into the repository interface statement
     *
     * @param string $repo
     * @return string
     */
    public function makeUseRepositoryInterface($repo)
    {
        return "{$this->makeRepositoryName($repo)}Interface";
    }

    /**
     * @param string $table
     * @param string $col
     * @return array
     */
    public function getRulesForColumn($table, $col)
    {
        if ($rules = Config::get("jackhammer.{$table}.rules.{$col}")){
            return $rules;
        }
        return [];
    }

    /**
     * @param string $table
     * @return array
     */
    public function getHiddenFields($table)
    {
        $hidden = Config::get("jackhammer.{$table}.hidden");
        $hidden = is_array($hidden) ? $hidden : [];
        array_push($hidden, 'id', 'created_at', 'updated_at');
        return $hidden;
    }

    /**
     *
     */
    public function save($name, $type, $view)
    {
        $dir = app_path() . '/' . Config::get("jackhammer.{$type}");
        if (!file_exists($dir)){
            mkdir($dir, 0700, true);
        }
        $file = "{$dir}/{$name}.php";
        if (!file_exists($file)){
            file_put_contents($file, $view);
        }
    }

}