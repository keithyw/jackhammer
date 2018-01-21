<?php

namespace Jackhammer\Console\Commands;

use Illuminate\Console\Command;
use Jackhammer\CoreTrait;

/**
 *
 * Class GenerateSimpleModel
 * @package Jackhammer\Console\Commands
 *
 */
class GenerateSimpleModel extends Command
{
    use CoreTrait;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'jackhammer:make-simple-model {model}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generates a Jackhammer Simple Model';

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
        $model = $this->makeObjectName($this->argument('model'));
        $table = $this->makeTableName($model);
        $view = view('jackhammer::simple_model', [
            'tableName' => $table,
            'header' => $this->header(),
        ]);
        try {
            $this->generateModelDir();
            $this->saveModel($model, $view);
        }
        catch (\Exception $e) {
            die($e->getMessage());
        }
    }
}
