<?php
/**
 * Created by PhpStorm.
 * User: chase
 * Date: 28/04/17
 * Time: 18:28
 */

namespace App\Repositories;


use App\Definitions\Data;
use App\Traits\Common;
use DB;
use Illuminate\Support\Collection;

class DataRepository
{
    use Common;

    /**
     * The table name
     * @var string
     */
    private $dataTable;

    /**
     * DataRepository constructor.
     */
    public function __construct()
    {
    }

    /**
     * Function used to set table for DataModel
     * @param string $tableName
     */
    public function setTable(string $tableName)
    {
        $this->dataTable = $tableName;
    }

    /**
     * Short function used to initialize the query builder
     * @return \Illuminate\Database\Query\Builder
     */
    protected function initQueryBuilder()
    {
        return DB::table($this->dataTable);
    }

    /**
     * Function used to find record by hashId
     * @param string $hashId
     * @return array
     */
    public function findByHash(string $hashId): array
    {
        //TODO: modify string hash_id to computed name

        $data = $this->findBy('hash_id', $hashId)->first();

        $data = $this->transformStdObjectToArray($data);

        return $data;
    }

    /**
     * Function used to find records by key with given value
     * @param string $key
     * @param string $value
     * @return \Illuminate\Database\Query\Builder
     */
    public function findBy(string $key, string $value)
    {
        return $this->initQueryBuilder()->where($key, $value);
    }

    /**
     * Function used to create a record
     * @param array $data
     */
    public function create(array $data)
    {
        $this->initQueryBuilder()->insert($data);
    }

    /**
     * Function used to update a record
     * @param array $data
     * @param array $whereClause
     * @param int $limit
     */
    public function update(array $data, array $whereClause, int $limit = 1)
    {
        $this->initQueryBuilder()->where($whereClause)->limit($limit)->update($data);
    }


    /**
     * Function used to fetch data based on given queries
     * @param array $queryData
     * @return \Illuminate\Support\Collection
     */
    public function fetchData(array $queryData): Collection
    {
        DB::enableQueryLog();

        /* Set the GROUP_CONCAT length */
        $this->setGroupConcatLength();

        /** @var \Illuminate\Database\Query\Builder $finalQuery */
        $finalQuery = null;

        foreach ($queryData as $data) {
            $this->setTable($data[Data::FETCH_QUERY_DATA_TABLE]);

            /* Add table to query */
            $query = $this->initQueryBuilder();

            /* Add select columns */
            $query->select(DB::raw(implode(', ', $data[Data::FETCH_QUERY_DATA_COLUMNS])));

            /* Add where clause */
            $query->where($data[Data::FETCH_QUERY_DATA_WHERE_CLAUSE]);

            /* Add group clause */
            $query->groupBy($data[Data::FETCH_QUERY_DATA_GROUP_CLAUSE]);

            if (is_null($finalQuery)) {
                $finalQuery = $query;
            } else {
                $finalQuery->union($query);
            }
        }

        /* Get data */
        $results = $finalQuery->get();

        //dd(DB::getQueryLog());
        return $results;
    }


    /**
     * Set GROUP_CONCAT value for current session
     * @param int $value (Default. 2^19)
     */
    protected function setGroupConcatLength($value = 524288)
    {
        DB::select(DB::raw("SET group_concat_max_len = $value"));
    }
    
}