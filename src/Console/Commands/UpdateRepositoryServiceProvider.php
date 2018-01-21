<?php
/**
 * Created by PhpStorm.
 * User: keithwatanabe
 * Date: 10/9/15
 * Time: 11:10 AM
 */

namespace Jackhammer\Console\Commands;

use Config;
use Illuminate\Console\Command;
use Illuminate\Foundation\Bus\DispatchesJobs;

/**
 * Class UpdateRepositoryServiceProvider
 * @package Conark\Jackhammer\Console\Commands
 */
class UpdateRepositoryServiceProvider extends Command
{
    use DispatchesJobs;

    private $_header = '<?php';

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'jackhammer:update-repository-provider';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Updates the repository service provider';

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
     * @param string $file
     * @return string
     */
    private function _getFilename($file)
    {
        return substr($file, strripos($file, '/') + 1, strlen($file));
    }

    /**
     * @param string $file
     * @return string
     */
    private function _getRepositoryName($file)
    {
        return substr($file, 0, strpos($file, '.php'));
    }

    /**
     * @param string $interface
     * return $string
     */
    private function _extractRepositoryName($interface)
    {
        $base = substr($interface, strripos($interface, '\\') + 1, strlen($interface));
        return str_replace('Interface', '', $base);
    }

    /**
     * @param string $name
     * @return array
     */
    private function _makeRepositoryInfo($name)
    {
        $path = 'App\\' . Config::get('jackhammer.repositories');
        return ['interface' => "{$path}\\{$name}Interface", 'repository' => "{$path}\\{$name}"];
    }
    /**
     * App\Repositories\UserRepositoryInterface
     * $this->app->bind('App\Services\ProductCodeServiceInterface', 'App\Services\ProductCodeService');
     *
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $serviceProviderName = Config::get('jackhammer.repositoryServiceProvider');
        $provider = app_path() . "/Providers/{$serviceProviderName}.php";
        $providerContent = file_get_contents($provider);
        if (!($repositoriesPath = Config::get('jackhammer.repositories'))) throw new \Exception('jackhammer repositories not defined');
        $repositoriesPath = app_path() . '/' . $repositoriesPath;
        $files = glob("{$repositoriesPath}/*Repository.php");
        $hash = [];
        $repositories = [];
        foreach ($files as $f){
            $repositoryFile = $this->_getFilename($f);
            $repository = $this->_getRepositoryName($repositoryFile);
            $interface = "{$repository}Interface";
            $hash[$repository] = ['file' => $repositoryFile, 'interface' => $interface];
            $repositories[]= $this->_makeRepositoryInfo($repository);
        }
        $lines = explode("\n", $providerContent);
        foreach ($lines as $line){
            if (stripos($line, '$this->app->bind')){
                if (preg_match('/\'(.*?)\'/', $line, $matches)){
                    $repo = $this->_extractRepositoryName($matches[1]);
                    if (!isset($hash[$repo])){
                        $hash[$repo] = ['interface' => "{$repo}Interface"];
                        $repositories[]= $this->_makeRepositoryInfo($repo);
                    }
                }
            }
        }
        $serviceProviderName = Config::get('jackhammer.repositoryServiceProvider');
        $provider = app_path() . "/Providers/{$serviceProviderName}.php";
        $view = view('jackhammer::provider', ['header' => $this->_header, 'repositories' => $repositories]);
        file_put_contents($provider, $view);
    }
}