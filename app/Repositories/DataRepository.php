<?php
/**
 * Created by PhpStorm.
 * User: chase
 * Date: 28/04/17
 * Time: 18:28
 */

namespace App\Repositories;


use App\Definitions\Columns;
use App\Definitions\Data;
use App\Models\ColumnModel;
use App\Services\ConfigGetter;
use DB;
use Illuminate\Database\Query\Builder;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Collection;

class DataRepository extends DefaultRepository
{

    /**
     * The table name
     * @var string
     */
    private $dataTable;

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
     * @return Builder
     */
    protected function initQueryBuilder()
    {
        return $this->getDataConnection()->table($this->dataTable);
    }

    /**
     * Function used to create a reporting table
     * @param Collection $columnDefinitions
     * @return array
     */
    public function createTable(Collection $columnDefinitions): array
    {
        try {
            $this->getDataSchema()->create($this->dataTable, function (Blueprint $table) use ($columnDefinitions) {
                $columnDefinitions->each(function (ColumnModel $columnModel) use (&$table) {

                    /* Create the column */
                    $column = $table->addColumn(
                        $columnModel->dataType,
                        $columnModel->name,
                        $columnModel->extra ?? []
                    );

                    if ($columnModel->allow_null) {
                        /* @noinspection PhpUndefinedMethodInspection */
                        $column->nullable();
                    }

                    /* Add index to column */
                    switch ($columnModel->index) {
                        case Columns::COLUMN_SIMPLE_INDEX:
                            $table->index($columnModel->name);
                            break;
                        case Columns::COLUMN_UNIQUE_INDEX:
                            $table->unique($columnModel->name);
                            break;
                        case Columns::COLUMN_PRIMARY_INDEX:
                            $table->primary($columnModel->name);
                            break;
                        default:
                            /* Add no index */
                            break;
                    }
                });

                $table->engine = 'InnoDB';
            });
        } catch (\Exception $exception) {
            return [
                false,
                $exception->getMessage()
            ];
        }

        return [
            /* Create table success status */
            true,
            /* Create table message if it failed */
            null
        ];
    }

    /**
     * Function used to check if tableName exists
     * @param string $tableName
     * @return bool
     */
    public function tableExists(string $tableName): bool
    {
        return $this->getDataSchema()->hasTable($tableName);
    }

    /**
     * Function used to find record by hashId
     * @param string $hashId
     * @return array
     */
    public function findByHash(string $hashId): array
    {
        $primaryKeyName = ((ConfigGetter::Instance())->primaryColumnData)[Data::CONFIG_COLUMN_NAME];

        $data = $this->findBy($primaryKeyName, $hashId)->first();

        $data = $this->transformStdObjectToArray($data);

        return $data;
    }

    /**
     * Function used to find records by key with given value
     * @param string $key
     * @param string $value
     * @return Builder
     */
    public function findBy(string $key, string $value)
    {
        return $this->initQueryBuilder()->where($key, $value);
    }

    /**
     * Function used to create a record
     * @param array $data
     */
    public function insert(array $data)
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
        /** @var Builder $finalQuery */
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

        return $results;
    }

}