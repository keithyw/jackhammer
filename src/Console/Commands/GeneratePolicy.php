<?php
/**
 * Created by PhpStorm.
 * User: keithwatanabe
 * Date: 10/29/15
 * Time: 3:07 PM
 */

namespace Jackhammer\Console\Commands;

use Jackhammer\CoreTrait;
use Config;
use Illuminate\Console\Command;

/**
 * Policies generated assume that the related resource contains
 * a relationship to the user object.
 *
 * Class GeneratePolicy
 * @package Conark\Jackhammer\Console\Commands
 */
class GeneratePolicy extends Command
{
    use CoreTrait;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'jackhammer:policy {model}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generates a policy using the configuration options in the jackhammer file';

    /**
     * @var Conark\Jackhammer\Models\BaseModel
     */
    protected $_model;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    private function _hasUpdate()
    {
        $policy = Config::get("jackhammer.{$this->argument('model')}.policy");
        return in_array('update', $policy);
    }

    private function _hasDelete()
    {
        $policy = Config::get("jackhammer.{$this->argument('model')}.policy");
        return in_array('delete', $policy);
    }

    /**
     *
     */
    public function handle()
    {
        $m = $this->argument('model');
        if (!is_array(Config::get("jackhammer.{$m}.policy"))) return;
        $arr = [
            'header' => $this->header(),
            'namespace' => $this->makeNamespace('policies'),
            'className' => $this->makeClassname($m, 'policy'),
            'modelNamespace' => $this->makeNamespace('models'),
            'model' => $this->makeObjectName($m),
            'modelVar' => $this->makeVariableName($m),
            'hasUpdate' => $this->_hasUpdate(),
            'hasDelete' => $this->_hasDelete()
        ];
        $this->save($arr['className'], 'policies', view('jackhammer::policy', $arr));
    }
}