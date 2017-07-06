<?php
/**
 * Created by PhpStorm.
 * User: chase
 * Date: 28/04/17
 * Time: 18:28
 */

namespace App\Repositories;


use App\Definitions\Data;
use DB;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Collection;

class SerialDataRepository extends DataRepository
{

    /**
     * Function used to fetch data based on given queries
     * @param array $queryData
     * @return \Illuminate\Support\Collection
     */
    public function fetchData(array $queryData): Collection
    {
        /** @var Builder $finalQuery */
        $finalQuery = null;

        foreach ($queryData as $data) {
            $this->setTable($data[Data::FETCH_QUERY_DATA_TABLE]);

            /* Add table to query */
            $query = $this->initQueryBuilder()
                /* Add select columns */
                ->select(DB::raw(implode(', ', $data[Data::FETCH_QUERY_DATA_COLUMNS])))
                /* Add where clause */
                ->where($data[Data::FETCH_QUERY_DATA_WHERE_CLAUSE])
                /* Add group clause */
                ->groupBy($data[Data::FETCH_QUERY_DATA_GROUP_CLAUSE]);

            if (is_null($finalQuery)) {
                $finalQuery = $query;
            } else {
                $finalQuery->union($query);
            }
        }

        /* Get data */
        $results = $finalQuery->get();

        return $results;
    }

}