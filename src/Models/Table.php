<?php namespace Conark\Jackhammer\Models;
/**
 * Created by PhpStorm.
 * User: keithwatanabe
 * Date: 9/30/15
 * Time: 1:48 PM
 */

class Table {

    /**
     * @var string
     */
    public $table;

    public function __construct($table){
        $this->table = $table;
    }
}