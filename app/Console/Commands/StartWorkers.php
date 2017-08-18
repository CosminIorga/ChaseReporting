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
    protected $signature = 'gearman:workers:start {workerCount=10}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Start X gearman workers';

    /**
     * Command runner
     */
    public function handle()
    {
        $workerCount = $this->argument('workerCount');

        $gearmanService = new GearmanWorkerService();

        $gearmanService->createAndDispatchWorkers('fetch_task', $workerCount);
    }
}
