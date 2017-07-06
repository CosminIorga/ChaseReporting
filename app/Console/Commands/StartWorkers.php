<?php

namespace App\Console\Commands;

use App\Services\GearmanWorkerService;
use Illuminate\Console\Command;

class StartWorkers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'gearman:worker';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';


    public function handle()
    {
        $this->testGearmanWorker();
    }

    protected function testGearmanWorker()
    {
        $gearmanService = new GearmanWorkerService();

        $gearmanService->createAndDispatchWorkers('fetch_task');
    }
}
