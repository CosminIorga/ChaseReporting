<?php
/**
 * Created by PhpStorm.
 * User: chase
 * Date: 28/04/17
 * Time: 18:28
 */

namespace App\Repositories;


use App\Traits\Common;
use DB;

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


}