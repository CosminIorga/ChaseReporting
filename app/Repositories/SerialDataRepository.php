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
    public function executeFetchOperations(array $queryData): Collection
    {
        $this->debug(self::class);

        $fetchData = $queryData[Data::OPERATION_FETCH_REPORTING_DATA][Data::FETCH_DATA];
        $fetchMode = $queryData[Data::OPERATION_FETCH_REPORTING_DATA][Data::FETCH_DATA_MODE];

        $query = $this->computeQuery($fetchData);

        /* Retrieve results if fetch_mode is set to SELECT */
        if ($fetchMode == Data::FETCH_DATA_MODE_SELECT) {
            $this->debug("FETCH_MODE = SELECT. Retrieving results");
            $this->shouldReturnFetchResults = true;

            $results = $query->get();

            return $results;
        }

        /* Otherwise compute insert into temp table operation */
        $this->debug("FETCH_MODE = INSERT. Preparing to insert data into temporary table");

        /* Get the bindings */
        $bindings = $query->getBindings();

        /* Create raw insert statement */
        $temporaryTable = $queryData[Data::OPERATION_CREATE_TABLE][Data::TEMPORARY_TABLE_NAME];
        $insertQuery = "INSERT INTO {$temporaryTable} {$query->toSql()} ";

        $insertSuccess = \DB::insert($insertQuery, $bindings);

        if (!$insertSuccess) {
            throw new FetchDataException(FetchDataException::FAILED_INSERTING_DATA_IN_TEMP_TABLE);
        }

        $this->debug("Parallel tasks executed successfully");

        /* Return empty collection as data will be fetched from temporary table */

        return collect();
    }

    /**
     * Function used to compute the query
     * @param array $fetchData
     * @return Builder
     */
    protected function computeQuery(array $fetchData): Builder
    {
        /** @var Builder $fullQuery */
        $fullQuery = null;

        foreach ($fetchData as $fetchRecord) {
            $this->setTable($fetchRecord[Data::FETCH_DATA_TABLE]);

            /* Add table to query */
            $query = $this->initQueryBuilder()
                /* Add select columns */
                ->select(DB::raw($this->stringifyFetchColumns($fetchRecord[Data::FETCH_DATA_COLUMNS])))
                /* Add where clause */
                ->where($fetchRecord[Data::FETCH_DATA_WHERE_CLAUSE])
                /* Add group clause */
                ->groupBy($fetchRecord[Data::FETCH_DATA_GROUP_CLAUSE]);

            $query = $this->addOrderByClauseToQuery($query, $fetchRecord[Data::FETCH_DATA_ORDER_CLAUSE]);

            if (is_null($fullQuery)) {
                $fullQuery = $query;
                continue;
            }

            $fullQuery->unionAll($query);
        }

        return $fullQuery;
    }

}