<?php
/**
 * Created by PhpStorm.
 * User: chase
 * Date: 05/07/17
 * Time: 13:44
 */

namespace App\Repositories;


use App\Definitions\Columns;
use App\Definitions\Data;
use App\Exceptions\FetchDataException;
use App\Models\ColumnModel;
use App\Services\ConfigGetter;
use DB;
use Illuminate\Database\Query\Builder;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Collection;


abstract class DataRepository extends DefaultRepository
{
    /**
     * The table name
     * @var string
     */
    protected $dataTable;

    /**
     * Flag used to indicate whether data should be retrieved from fetchOperations or postFetchOperations
     * @var bool
     */
    protected $shouldReturnFetchResults = false;

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
    protected function initQueryBuilder(): Builder
    {
        return $this->getDataConnection()->table($this->dataTable);
    }

    /**
     * Function used to create a reporting table
     * @param Collection $columnDefinitions
     * @param string $tableName
     * @return array
     */
    public function createTable(Collection $columnDefinitions, $tableName = null): array
    {
        try {
            $tableName = $tableName ?? $this->dataTable;

            $this->getDataSchema()->create($tableName, function (Blueprint $table) use ($columnDefinitions) {
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
     * @return Collection
     * @throws FetchDataException
     */
    public function fetchData(array $queryData): Collection
    {
        if (env('APP_DEBUG') == true) {
            DB::enableQueryLog();
        }

        /* Reset flag to false */
        $this->shouldReturnFetchResults = false;

        /* Surround operations in try-catch in order to revert modifications like table creation */
        try {
            /* Check and execute pre-fetch operations if they exist */
            $this->executePreFetchOperations($queryData);

            /* Execute fetch operations */
            $fetchResults = $this->executeFetchOperations($queryData);

            /* Check and execute post-fetch operations if they exist */
            $postFetchResults = $this->executePostFetchOperations($queryData);
        } catch (FetchDataException $exception) {
            $this->revertOperations($queryData);

            throw new FetchDataException($exception->getMessage());
        }

        if (env('APP_DEBUG') == true) {
            dump(\DB::getQueryLog());
        }

        if ($this->shouldReturnFetchResults) {
            return $fetchResults;
        }

        return $postFetchResults;
    }

    /**
     * Function used to execute pre-fetch operations if they exist
     * @param array $queryData
     * @throws FetchDataException
     */
    protected function executePreFetchOperations(array $queryData)
    {
        /* Check if "create_table" operation exists */
        if (!array_key_exists(Data::OPERATION_CREATE_TABLE, $queryData)) {
            return;
        }

        /* Create temporary table */
        list($success, $errorMessage) = $this->createTable(
            $queryData[Data::OPERATION_CREATE_TABLE][Data::TEMPORARY_TABLE_COLUMN_DEFINITIONS],
            $queryData[Data::OPERATION_CREATE_TABLE][Data::TEMPORARY_TABLE_NAME]
        );

        if (!$success) {
            throw new FetchDataException($errorMessage);
        }
    }

    /**
     * Function used to execute post fetch operations
     * @param array $queryData
     * @return Collection
     */
    protected function executePostFetchOperations(array $queryData): Collection
    {
        /* Do nothing if no "fetch_temporary_data" operation exists */
        if (!array_key_exists(Data::OPERATION_FETCH_TEMPORARY_DATA, $queryData)) {
            return collect();
        }

        /* Fetch data from temporary table */
        $fetchTemporaryDataOperation = $queryData[Data::OPERATION_FETCH_TEMPORARY_DATA];
        $this->setTable($fetchTemporaryDataOperation[Data::FETCH_DATA_TABLE]);

        /* Add table to query */
        $query = $this->initQueryBuilder()
            /* Add select columns */
            ->select(DB::raw($this->stringifyFetchColumns($fetchTemporaryDataOperation[Data::FETCH_DATA_COLUMNS])))
            /* Add group clause */
            ->groupBy($fetchTemporaryDataOperation[Data::FETCH_DATA_GROUP_CLAUSE]);

        $data = $query->get();

        /* Drop temporary table */
        $dropTemporaryTableOperation = $queryData[Data::OPERATION_DROP_TABLE];

        \Schema::drop($dropTemporaryTableOperation[Data::FETCH_DATA_TABLE]);

        return $data;
    }

    /**
     * Function used to revert operations if something goes wrong
     * @param array $queryData
     */
    protected function revertOperations(array $queryData)
    {

    }

    /**
     * Function used to stringify query columns
     * @param array $fetchColumns
     * @return string
     */
    protected function stringifyFetchColumns(array $fetchColumns): string
    {
        $stringedColumns = array_map(function($columnAlias, $syntax){
            return "$syntax AS $columnAlias";
        }, array_keys($fetchColumns), array_values($fetchColumns));

        return implode(', ', $stringedColumns);
    }

    /**
     * Function used to execute the fetch operations and retrieve data or insert it into a temporary table
     * @param array $queryData
     * @return Collection
     * @throws FetchDataException
     */
    abstract protected function executeFetchOperations(array $queryData): Collection;
}