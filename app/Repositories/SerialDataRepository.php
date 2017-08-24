<?php
/**
 * Created by PhpStorm.
 * User: chase
 * Date: 28/04/17
 * Time: 18:28
 */

namespace App\Repositories;


use App\Definitions\Data;
use App\Exceptions\FetchDataException;
use DB;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Collection;

class SerialDataRepository extends DataRepository
{

    /**
     * Function used to execute the fetch operations and retrieve data or insert it into a temporary table
     * @param array $queryData
     * @return Collection
     * @throws FetchDataException
     */
    protected function executeFetchOperations(array $queryData): Collection
    {
        $fetchData = $queryData[Data::OPERATION_FETCH_REPORTING_DATA][Data::FETCH_DATA];
        $fetchMode = $queryData[Data::OPERATION_FETCH_REPORTING_DATA][Data::FETCH_DATA_MODE];

        /** @var Builder $finalQuery */
        $finalQuery = null;

        foreach ($fetchData as $fetchDataRecord) {
            $this->setTable($fetchDataRecord[Data::FETCH_DATA_TABLE]);

            /* Add table to query */
            $query = $this->initQueryBuilder()
                /* Add select columns */
                ->select(DB::raw($this->stringifyFetchColumns($fetchDataRecord[Data::FETCH_DATA_COLUMNS])))
                /* Add where clause */
                ->where($fetchDataRecord[Data::FETCH_DATA_WHERE_CLAUSE])
                /* Add group clause */
                ->groupBy($fetchDataRecord[Data::FETCH_DATA_GROUP_CLAUSE]);

            if (is_null($finalQuery)) {
                $finalQuery = $query;
            } else {
                $finalQuery->union($query);
            }
        }

        /* Return data if fetch_mode is set to select */
        if ($fetchMode == Data::FETCH_DATA_MODE_SELECT) {
            $this->shouldReturnFetchResults = true;

            return $finalQuery->get();
        }

        /* Otherwise compute insert into temp table operation */
        /* Get the bindings */
        $bindings = $finalQuery->getBindings();

        /* Create raw insert statement */
        $temporaryTable = $queryData[Data::OPERATION_CREATE_TABLE][Data::TEMPORARY_TABLE_NAME];
        $insertQuery = "INSERT INTO {$temporaryTable} {$finalQuery->toSql()} ";

        $insertSuccess = \DB::insert($insertQuery, $bindings);

        if (!$insertSuccess) {
            throw new FetchDataException(FetchDataException::FAILED_INSERTING_DATA_IN_TEMP_TABLE);
        }

        return collect();
    }


}