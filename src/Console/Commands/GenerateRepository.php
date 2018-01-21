<?php

namespace Jackhammer\Console\Commands;

use Config;
use Illuminate\Console\Command;

class GenerateRepository extends Command
{
    use \Jackhammer\CoreTrait;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'jackhammer:make-repository {model}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Creates a Repository and an interface';

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
     * 
     */
    public function handle()
    {
        try {
            $model = $this->makeObjectName($this->argument('model'));
            $this->generateRepositoryContractDir();
            $this->generateRepositoryDir();
            $repo = $this->makeRepositoryName($model);
            $modelPath = Config::get('jackhammer.models');
            $interfaceView = view('jackhammer::repository_interface', ['interface' => $repo, 'header' => $this->header()]);
            $view = view('jackhammer::repository', [
                'header' => $this->header(),
                'model' => $model,
                'modelPath' => $modelPath,
                'interface' => $repo,
                'className' => $repo,
                'repositoryContractPath' => $this->getRepositoryContractNamespace(),
            ]);
            $this->saveRepositoryContract($repo, $interfaceView);
            $this->saveRepository($repo, $view);
        }
        catch (\Exception $e) {
            die($e->getMessage());
        }
    }
}
