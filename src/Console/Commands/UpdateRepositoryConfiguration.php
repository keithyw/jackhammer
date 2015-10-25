<?php
/**
 * Created by PhpStorm.
 * User: keithwatanabe
 * Date: 10/14/15
 * Time: 12:08 PM
 */

namespace Conark\Jackhammer\Console\Commands;

use Config;
use Illuminate\Console\Command;

class UpdateRepositoryConfiguration extends Command
{
    // i really need to do something about this
    private $_header = '<?php';

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'jackhammer:update-repository-configuration';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Updates the repository configuration file by adding the basic model elements and sample times';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Look up models/repositories and see which ones have a corresponding entry
     * in the repository.php configuration file. If there is no entry, we'll
     * add it as a key with some default values then re-populate the existing
     * config.
     *
     * 'time' => 60,
    'tag' => 'addresses',
     *
     *
     * @throws \Exception
     */
    public function handle()
    {
        if (!($config = Config::get('repositories'))) throw new \Exception("repositories.php not setup");
        if (!($modelPath = Config::get('jackhammer.models'))) throw new \Exception('jackhammer models not defined');
        $path = app_path() . '/' . $modelPath;
        $files = glob("{$path}/*.php");
        foreach ($files as $file){
            $file = substr($file, strripos($file, '/') + 1, strlen($file));
            $modelName = str_plural(snake_case(str_replace('.php', '', $file)));
            if (!isset($config[$modelName])){
                $config[$modelName] = [
                    'time' => 60,
                    'tag' => $modelName
                ];
            }
        }

        $view = view('jackhammer::repository_config', ['header' => $this->_header, 'items' => $config]);
        $file = config_path() . '/repositories.php';
        file_put_contents($file, $view);
    }
}