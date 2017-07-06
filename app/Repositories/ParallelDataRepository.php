<?php
/**
 * Created by PhpStorm.
 * User: chase
 * Date: 04/07/17
 * Time: 14:15
 */

namespace App\Repositories;


use App\Definitions\Gearman;
use App\Helpers\GearmanServiceHelper;
use App\Services\GearmanService;
use Illuminate\Support\Collection;

class ParallelDataRepository extends DataRepository
{
    /**
     * The gearman service
     * @var GearmanService
     */
    protected $gearmanService;


    /**
     * ParallelDataRepository constructor.
     */
    public function __construct()
    {
        $this->init();
    }

    /**
     * Function used to initialize various variables
     */
    public function init()
    {
        $this->gearmanService = new GearmanService();

        /* Add gearman server */
        $this->gearmanService->addServer();
    }

    /**
     * Function used to fetch data in parallel based on given queries
     * @param array $queryData
     * @return \Illuminate\Support\Collection
     */
    public function fetchData(array $queryData): Collection
    {
        foreach ($queryData as $queryDataRecord) {
            $this->gearmanService->addTask(
                Gearman::FETCH_TASK,
                GearmanServiceHelper::encodeWorkload($queryDataRecord)
            );
        }

        $this->gearmanService->runTasks();

        return collect($this->gearmanService->retrieveResponse());
    }
}