<?php
/**
 * Created by PhpStorm.
 * User: chase
 * Date: 05/07/17
 * Time: 15:27
 */

namespace App\Helpers;


use App\Definitions\Data;
use App\Definitions\Gearman;
use App\Traits\LogHelper;

class GearmanServiceHelper
{
    use LogHelper;

    /**
     * GearmanServiceHelper constructor.
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
     * Function used to encode the gearman workload
     * @param array $workload
     * @return string
     */
    public static function encodeWorkload(array $workload): string
    {
        return serialize($workload);
    }

    /**
     * Function used to decode the gearman workload
     * @param string $workload
     * @return array
     */
    public static function decodeWorkload(string $workload): array
    {
        return unserialize($workload);
    }

    /**
     * Short function used to return the gearman worker id
     * @param string $context
     * @return string
     */
    public static function getUniqueId(string $context): string
    {
        $context = self::decodeWorkload($context);

        return $context[Gearman::WORKER_ID];
    }

    /**
     * Function called exclusively by Gearman when a "fetch" task is called asynchronously
     * @param \GearmanJob $job
     * @return string
     */
    public function fetchDataUsingGearmanNode(\GearmanJob $job): string
    {
        try {
            $data = self::decodeWorkload($job->workload());

            /* Init query */
            $query = \DB::connection('data_connection')
                ->table($data[Data::FETCH_QUERY_DATA_TABLE])
                /* Add select columns */
                ->select(\DB::raw(implode(', ', $data[Data::FETCH_QUERY_DATA_COLUMNS])))
                /* Add where clause */
                ->where($data[Data::FETCH_QUERY_DATA_WHERE_CLAUSE])
                /* Add group clause */
                ->groupBy($data[Data::FETCH_QUERY_DATA_GROUP_CLAUSE]);

            /* Fetch data */
            $results = $query->get();

            return self::encodeWorkload($results->toArray());
        } catch (\Exception $exception) {
            $this->error($exception->getMessage());
            $job->sendException($exception);

            return null;
        }
    }
}