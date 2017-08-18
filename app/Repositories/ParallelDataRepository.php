<?php
/**
 * Created by PhpStorm.
 * User: chase
 * Date: 04/07/17
 * Time: 14:15
 */

namespace App\Repositories;


use App\Definitions\Data;
use App\Definitions\Gearman;
use App\Exceptions\FetchDataException;
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
     * Function used to execute the fetch operations and retrieve data or insert it into a temporary table
     * @param array $queryData
     * @return Collection
     * @throws FetchDataException
     */
    public function executeFetchOperations(array $queryData): Collection
    {
        /* Fetch_Mode is always set to INSERT for parallel fetching */
        $fetchData = $queryData[Data::OPERATION_FETCH_REPORTING_DATA][Data::FETCH_DATA];

        /* Check if fetchData contains only one table. Call serialRepository if so */
        if (count($fetchData) == 1) {
            /* Set flag in order to know to return the fetch results */
            $this->shouldReturnFetchResults = true;

            return (new SerialDataRepository())->fetchData($queryData);
        }

        /* Otherwise prepare data for parallel fetching */
        foreach ($fetchData as $queryDataRecord) {
            $payload = [
                Data::TEMPORARY_TABLE_NAME => $queryData[Data::OPERATION_CREATE_TABLE][Data::TEMPORARY_TABLE_NAME],
                Data::QUERY_DATA => $queryDataRecord
            ];

            $this->gearmanService->addTask(
                Gearman::FETCH_TASK,
                GearmanServiceHelper::encodeWorkload($payload)
            );
        }

        $this->gearmanService->runTasks();

        $response = $this->gearmanService->retrieveResponse();

        /* Iterate through responses and check if all went well */
        foreach ($response as $nodeStatus) {
            if ($nodeStatus[Data::INSERTION_STATUS] == false) {
                throw new FetchDataException(FetchDataException::NODE_FAILED_TO_INSERT_DATA_IN_TEMP_TABLE);
            }
        }

        /* Otherwise return empty collection as data will be fetched from temporary table */
        return collect();
    }



}
