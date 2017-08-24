<?php
/**
 * Created by PhpStorm.
 * User: chase
 * Date: 05/07/17
 * Time: 17:06
 */

namespace App\Services;


use App\Definitions\Gearman;
use App\Helpers\GearmanServiceHelper;
use App\Traits\LogHelper;
use GearmanWorker;

class GearmanWorkerService
{
    use LogHelper;

    /**
     * GearmanService constructor.
     */
    public function __construct()
    {
        $this->init();
    }

    /**
     * Function used to initialize various variables
     */
    protected function init()
    {
        $this->setChannel('gearman');
    }

    /**
     * Function used to create and add a number of workers to the Gearman workers pool
     * Warning: This is a blocking function which runs until the workers are killed
     * @param string $workerTask
     * @param int $workerCount
     */
    public function createAndDispatchWorkers(string $workerTask, int $workerCount = 10)
    {
        /* Create workers */
        $workers = $this->createWorkers($workerTask, $workerCount);

        /* Dispatch workers */
        $this->dispatchWorkers($workers);
    }

    /**
     * Function used to create a number of workers
     * @param string $workerTask
     * @param int $workerCount
     * @return array
     */
    protected function createWorkers(string $workerTask, int $workerCount): array
    {
        $workers = [];

        foreach (range(0, $workerCount - 1) as $workerId) {
            $worker = new GearmanWorker();
            $worker->addServer();
            $worker->addFunction(
                $workerTask,
                [
                    new GearmanServiceHelper(),
                    Gearman::GEARMAN_FUNCTION_MAPPING[$workerTask],
                ]
            );

            $workers[$workerId] = $worker;
        }

        return $workers;
    }

    /**
     * Function used to dispatch workers
     * @param array $workers
     */
    protected function dispatchWorkers(array $workers)
    {
        $totalWorkers = count($workers);
        $this->debug("Starting $totalWorkers workers ... ");

        /* Detach from parent process and create worker instances */
        array_walk($workers, function (GearmanWorker $worker, int $workerId) {
            $pid = pcntl_fork();

            /* Only for child process */
            if ($pid == 0) {
                while ($worker->work()) {
                    true;
                }

                exit($workerId);
            }
        });

        $this->debug("Successfully detached and started workers");
    }


}