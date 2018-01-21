<?php
/**
 * Created by PhpStorm.
 * User: keithwatanabe
 * Date: 1/21/18
 * Time: 11:03 AM
 */

namespace Jackhammer\Providers;

use Config;
use Illuminate\Support\ServiceProvider;

/**
 * Class RepositoryServiceProvider
 * @package Jackhammer\Providers
 */
class RepositoryServiceProvider extends ServiceProvider
{
    use \Jackhammer\CoreTrait;

    /**
     *
     */
    public function register()
    {
        $contractDir = $this->getRepositoryContractDir();
        $repositoryDir = $this->getRepositoryDir();
        $contractFiles = glob("{$contractDir}/*.php");
        $contractNamespace = $this->makeRepositoryContractNamespace();
        $repositoryNamespace = $this->makeRepositoryNamespace();
        foreach ($contractFiles as $contractFile) {
            $path = pathinfo($contractFile);
            $repo = "{$repositoryDir}/{$path['basename']}";
            $file = $path['filename'];
            if (file_exists($repo)) {
                $this->app->bind("{$contractNamespace}\\{$file}", "{$repositoryNamespace}\\{$file}");
            }
        }
    }
}