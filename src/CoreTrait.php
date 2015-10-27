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
     * converts the repo (table name) into the repository interface statement
     *
     * @param string $repo
     * @return string
     */
    public function makeUseRepositoryInterface($repo)
    {
        return $this->makeObjectName($repo) . "RepositoryInterface";
    }
}