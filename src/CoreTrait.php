<?php
/**
 * Created by PhpStorm.
 * User: keithwatanabe
 * Date: 10/23/15
 * Time: 11:53 AM
 */

namespace Conark\Jackhammer;


trait CoreTrait {

    /**
     * @throws \Exception
     */
    public function loadModel()
    {
        if (!$this->_model){
            $model = str_singular(studly_case($this->argument('model')));
            if (!($modelPath = Config::get('jackhammer.models'))) throw new \Exception('jackhammer models not defined');
            $modelFile = 'App\\' . str_replace('/', '\\', "{$modelPath}/{$model}");
            $this->_model = new $modelFile();
        }
        return $this->_model;
    }

    /**
     * converts the repo (table name) into the repository interface statement
     *
     * @param string $repo
     * @return string
     */
    public function makeUseRepositoryInterface($repo){
        return str_singular(studly_case($repo)) . "RepositoryInterface";
    }
}