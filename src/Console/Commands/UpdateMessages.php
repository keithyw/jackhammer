<?php
/**
 * Created by PhpStorm.
 * User: keithwatanabe
 * Date: 10/22/15
 * Time: 2:35 PM
 */
namespace Conark\Jackhammer\Console\Commands;

use Config;
use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;

/**
 * Updates the messages resource file with some standard messaging for templates
 *
 * Class UpdateMessages
 * @package Conark\Jackhammer\Console\Commands
 */
class UpdateMessages extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'jackhammer:update-messages';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Regenerates the messages file with language strings mostly for admin';

    /**
     * @var Illuminate\Filesystem\Filesystem;
     */
    protected $files;

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

    /**
     * @param string $file
     * @return string
     */
    private function _getFilename($file)
    {
        return substr($file, strripos($file, '/') + 1, strlen($file));
    }

    /**
     * i really fucking need a standard spot for these functions.
     * halppppp
     *
     * @return string
     */
    private function _getMessageFile()
    {
        return base_path('/resources/lang/en/messages.php');
    }

    /**
     * @return array
     */
    private function _getModels()
    {
        $modelPath = app_path('/' . Config::get('jackhammer.models'));
        $files = glob("{$modelPath}/*.php");
        $models = [];
        foreach ($files as $f){
            $models[]= str_replace('.php', '', $this->_getFilename($f));
        }
        return $models;
    }

    private function _loadModel($model)
    {
        if (!($modelPath = Config::get('jackhammer.models'))) throw new \Exception('jackhammer models not defined');
        $modelFile = "App\\{$modelPath}\\{$model}";
        return new $modelFile();
    }

    /**
     * @param array $fields
     * @return array
     */
    private function _generateFormFields($fields)
    {
        $ret = [];
        foreach ($fields as $f){
            $ret["form_field_{$f}"] = ucwords(str_replace('_', ' ', $f));
        }
        return $ret;
    }

    /**
     * @param string $out
     */
    private function _save($out)
    {
        $file = base_path("resources/lang/en/messages.php");
        //if (!file_exists($file)){
        $this->files->put($file, $out);
        //}
    }

    /**
     * @param string $model
     * @param array $fields
     */
    private function _mapMessages($model, &$fields)
    {
        $model = snake_case($model);
        $items = Config::get('jackhammer.messages');
        foreach ($items as $k => $v){
            $fields["{$model}_{$k}"] = ucwords(str_replace('_', ' ', $model)) . " {$v}";
        }
    }

    /**
     *'permission_create' => 'Create Permission',
    'permission_edit' => 'Edit Permission',
    'permissions_delete_success' => 'Permission successfully deleted',
    'permissions_index_title' => 'Permissions',
    'permissions_store_success' => 'Permission successfully created',
    'permissions_update_success' => 'Permission successfully updated',
     */
    public function handle()
    {
        $ret = $this->files->getRequire($this->_getMessageFile());
        $models = $this->_getModels();
        foreach ($models as $model){
            $obj = $this->_loadModel($model);
            $fields = $this->_generateFormFields($obj->getFillable());
            $ret = array_merge($ret, $fields);
            $this->_mapMessages($model, $ret);
        }
        ksort($ret);
        $this->_save(view('jackhammer::messages', ['messages' => $ret, 'header' => '<?php']));
    }

}