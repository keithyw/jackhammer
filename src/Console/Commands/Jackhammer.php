<?php

namespace Conark\Jackhammer\Console\Commands;

use Config;
use DB;
use Illuminate\Console\Command;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Conark\Jackhammer\Jobs\InspectTable;

/**
 * Tasks:
 * 1) authorization on a per-resource basis (policy generation)
 * 2) smart/configuration based validation (models/request)
 * 3) generate request form objects for admin/REST(?)
 * 4) figure out how to insert/update/remove associations
 * 5) start to split up job object since it's growing large
 * 6) generate admin into either React JS
 * 7)
 *
 * For policies, have controllers load the policy.
 * Then in the core controllers, check to see if the policy
 * exist. If it does, then run the check there.
 *
 * Or have the repository handle the check. Not sure which one is better
 * right now. Or perhaps both.
 *
 * Maybe modify controller to start and see how it feels
 *
 * Class Jackhammer
 * @package Conark\Jackhammer\Console\Commands
 */
class Jackhammer extends Command
{
    use DispatchesJobs;
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'jackhammer:jack {--table=} {--database=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Scaffold from a table';

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
     * @param string $model
     * @param string $database
     */
    private function _work($table, $database){
        $model = str_singular(snake_case($table));
        $this->dispatch(new InspectTable($table, $database));
        $this->callSilent('jackhammer:update-repository-configuration', []);
        $this->callSilent('jackhammer:update-repository-provider');
        $this->callSilent('jackhammer:transformer', ['model' => $model]);
        $this->callSilent('jackhammer:policy', ['model' => $model]);
        $this->callSilent('jackhammer:rest-controller', ['model' => $model]);
        $this->callSilent('jackhammer:admin-controller', ['model' => $model]);
        $this->callSilent('jackhammer:admin-templates', ['model' => $model]);
        $this->callSilent('jackhammer:update-routes');
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        if ($table = $this->option('table')){
            $this->_work($table, $this->option('database'));
        }
        if ($database = $this->option('database')){
            $tables = DB::table('information_schema.tables')
                ->where('table_schema', '=', $database)
                ->get();
            $ignore = Config::get('jackhammer.ignore_tables');
            foreach ($tables as $table){
                if (!in_array($table, $ignore)){
                    $this->_work($table->TABLE_NAME, $database);
                }
            }
        }

    }
}
