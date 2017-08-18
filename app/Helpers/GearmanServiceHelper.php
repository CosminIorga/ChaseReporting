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
            /* Start timer for performance benchmarks */
            $startTime = microtime(true);

            $data = self::decodeWorkload($job->workload());

            $queryData = $data[Data::QUERY_DATA];
            $temporaryTable = $data[Data::TEMPORARY_TABLE_NAME];

            /* Init query */
            $query = \DB::connection('data_connection')
                ->table($queryData[Data::FETCH_DATA_TABLE])
                /* Add select columns */
                ->select(\DB::raw(implode(', ', $queryData[Data::FETCH_DATA_COLUMNS])))
                /* Add where clause */
                ->where($queryData[Data::FETCH_DATA_WHERE_CLAUSE])
                /* Add group clause */
                ->groupBy($queryData[Data::FETCH_DATA_GROUP_CLAUSE]);

            /* Get the bindings */
            $bindings = $query->getBindings();

            /* Create raw insert statement */
            $insertQuery = "INSERT INTO {$temporaryTable} {$query->toSql()}";

            /* Insert data */
            $insertSuccess = \DB::insert($insertQuery, $bindings);

            /* Compute total operations time */
            $endTime = microtime(true);
            $elapsed = $endTime - $startTime;

            $this->debug("Finished one operation in $elapsed seconds");
            /* Return status of whether insertion occurred successfully or not */
            return self::encodeWorkload([
                Data::INSERTION_STATUS => (bool) $insertSuccess,
            ]);
        } catch (\Exception $exception) {
            $this->error($exception->getMessage());
            $job->sendException($exception);

            return self::encodeWorkload([
                Data::INSERTION_STATUS => false
            ]);
        }
    }
}