<?php
/**
 * Created by PhpStorm.
 * User: keithwatanabe
 * Date: 10/23/15
 * Time: 11:53 AM
 */

namespace Jackhammer;

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
     * @return string
     */
    public function getModelDir()
    {
        if (!($modelPath = Config::get('jackhammer.models'))) throw new \Exception('jackhammer models not defined');
        return app_path() . '/' . $modelPath;
    }

    /**
     * @return bool
     * @throws \Exception
     */
    public function generateModelDir()
    {
        $dir = $this->getModelDir();
        if (file_exists($dir)) {
            echo "{$dir} already exist\n";
            return false;
        }
        if (mkdir($dir)) {
            echo "{$dir} has been created\n";
            return true;
        }
        return false;
    }

    /**
     * @param string $name
     * @param string $view
     * @return bool
     * @throws \Exception
     */
    public function saveModel($name, $view)
    {
        $file = "{$this->getModelDir()}/{$name}.php";
        if (file_exists($file)) {
            echo "{$file} aready exist. Skipping\n";
            return false;
        }
        if (file_put_contents($file, $view)) {
            echo "{$file} was written\n";
            return true;
        }
        return false;
    }

    /**
     * @param string $model
     * @return string
     */
    public function getModelFile($model)
    {
        return "{$this->getModelDir()}/{$model}.php";
    }

    /**
     * @return string
     */
    public function getRepositoryDir()
    {
        if (!($repositoryPath = Config::get('jackhammer.repositories'))) throw new \Exception('jackhammer repositories not defined');
        return app_path() . '/' . $repositoryPath;
    }

    /**
     * @return string
     */
    public function getTransformerDir()
    {
        if (!($transformerPath = Config::get('jackhammer.transformers'))) throw new \Exception('jackhammer transformers not defined');
        return app_path() . '/' . $transformerPath;
    }

    /**
     * @param string $repo
     * @return string
     */
    public function getRepositoryFile($repo)
    {
        return "{$this->getRepositoryDir()}/{$repo}Repository.php";
    }

    /**
     * @param string $file
     */
    public function checkFile($file)
    {
        if (!file_exists($file)) throw new \Exception("{$file} does not exist");
    }

    /**
     * @param string $policy
     * @return bool
     */
    public function hasPolicy($policy)
    {
        return is_array(Config::get("jackhammer.{$policy}.policy"));
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

    public function makeTransformerNamespace()
    {
        return $this->makeNamespace('transformer');
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
     * @param $name
     * @return string
     */
    public function makeTableName($name)
    {
        return str_plural(snake_case($name));
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
     * @return string
     */
    public function makeModelNamespace()
    {
        return $this->makeNamespace('models');
    }

    /**
     * @return string
     */
    public function makePolicyNamespace()
    {
        return $this->makeNamespace('policies');
    }
    /**
     * @return string
     */
    public function makeRepositoryNamespace()
    {
        return $this->makeNamespace('repositories');
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