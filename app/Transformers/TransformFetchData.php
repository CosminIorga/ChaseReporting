<?php
/**
 * Created by PhpStorm.
 * User: chase
 * Date: 19/05/17
 * Time: 17:41
 */

namespace App\Transformers;


use App\Definitions\Columns;
use App\Definitions\Data;
use App\Definitions\Functions;
use App\Models\ColumnModel;
use App\Services\ConfigGetter;
use App\Services\Utils;
use App\Traits\OutputFunctions;

class TransformFetchData
{
    use OutputFunctions;

    /**
     * The data to be parsed
     * @var array
     */
    protected $fetchData;

    /**
     * The parsed data to be returned
     * @var array
     */
    protected $operations = [];

    /**
     * Array used to store the information regarding the reporting tables and columns from which to fetch data
     * @var array
     */
    protected $tablesAndIntervals;

    /**
     * The config getter
     * @var ConfigGetter
     */
    protected $configGetter;

    /**
     * Flag used to identify if data fetching will be made from multiple tables
     * @var bool
     */
    protected $fetchFromMultipleTables;

    /**
     * Variable used to hold the temporary table name
     * @var string
     */
    protected $temporaryTableName;

    /**
     * Small function used to initialize fields
     * @param array $fetchData
     * @param array $tablesAndIntervals
     */
    protected function init(array $fetchData, array $tablesAndIntervals)
    {
        $this->fetchData = $fetchData;
        $this->configGetter = ConfigGetter::Instance();
        $this->tablesAndIntervals = $tablesAndIntervals;
        $this->fetchFromMultipleTables = count($this->tablesAndIntervals) > 1;
        $this->temporaryTableName = null;
    }

    /**
     * Transform fetch data using the tablesAndColumns array to create an array of arrays
     * with necessary information to perform a single query from each sub-array
     * @param array $fetchData
     * @param array $tablesAndIntervals
     * @return array
     */
    public function toReportingData(array $fetchData, array $tablesAndIntervals): array
    {
        $this->init($fetchData, $tablesAndIntervals);

        /* Check if number of tables to fetch data from is greater than one */
        if ($this->fetchFromMultipleTables) {
            $this->operations = [
                /* Compute create temporary table operation */
                Data::OPERATION_CREATE_TABLE => $this->computeCreateTemporaryTableOperation(),
                /* Compute the fetch data from temporary table operation */
                Data::OPERATION_FETCH_TEMPORARY_DATA => $this->computeFetchDataFromTemporaryTableOperation(),
                /* Compute drop temporary table operation */
                Data::OPERATION_DROP_TABLE => $this->computeDropTemporaryTableOperation(),
            ];
        }

        /* Regardless of number of tables, add the fetch data from reporting tables operation */
        $this->operations[Data::OPERATION_FETCH_REPORTING_DATA] = $this->computeFetchDataFromReportingTableOperations();

        return $this->operations;
    }


    /**
     * Function used to compute the temporary table name and column definitions
     * @return array
     */
    protected function computeCreateTemporaryTableOperation(): array
    {
        $columnDefinitions = collect();

        /* Add group by columns to columnDefinitions */
        foreach ($this->fetchData[Data::GROUP_CLAUSE] as $groupColumn) {
            $groupColumnConfig = $this->configGetter->getPivotConfigByName($groupColumn);

            $columnDefinitions->push(new ColumnModel([
                ColumnModel::COLUMN_NAME => $groupColumnConfig[Data::CONFIG_COLUMN_NAME],
                ColumnModel::COLUMN_DATA_TYPE => $groupColumnConfig[Data::CONFIG_COLUMN_DATA_TYPE],
                ColumnModel::COLUMN_INDEX => Columns::COLUMN_SIMPLE_INDEX,
                ColumnModel::COLUMN_ALLOW_NULL => $groupColumnConfig[Data::CONFIG_COLUMN_ALLOW_NULL],
                ColumnModel::COLUMN_EXTRA_PARAMETERS => [
                    ColumnModel::COLUMN_DATA_TYPE_LENGTH =>
                        $groupColumnConfig[Data::CONFIG_COLUMN_DATA_TYPE_LENGTH] ?? null,
                ],
            ]));
        }

        /* Add "aggregateColumn" */
        $columnDefinitions->push(new ColumnModel([
            ColumnModel::COLUMN_NAME => Data::TEMPORARY_TABLE_AGGREGATE_COLUMN_NAME,
            ColumnModel::COLUMN_DATA_TYPE => Columns::COLUMN_DATA_TYPE_JSON,
            ColumnModel::COLUMN_INDEX => Columns::COLUMN_NO_INDEX,
            ColumnModel::COLUMN_ALLOW_NULL => false,
            ColumnModel::COLUMN_EXTRA_PARAMETERS => [],
        ]));

        return [
            Data::TEMPORARY_TABLE_NAME => $this->computeTemporaryTableName(),
            Data::TEMPORARY_TABLE_COLUMN_DEFINITIONS => $columnDefinitions,
        ];
    }

    /**
     * Function used to compute the information needed to fetch data from the temporary table
     * @return array
     */
    protected function computeFetchDataFromTemporaryTableOperation(): array
    {
        return [
            Data::FETCH_DATA_TABLE => $this->computeTemporaryTableName(),
            Data::FETCH_DATA_COLUMNS => $this->computeColumns(
                $this->computeFetchTemporaryColumns($this->fetchData[Data::COLUMNS]),
                [Data::TEMPORARY_TABLE_AGGREGATE_COLUMN_NAME],
                false,
                true
            ),
            Data::FETCH_DATA_GROUP_CLAUSE => $this->computeQueryGroupClause(),
        ];
    }

    /**
     * Function used to compute the information needed to drop the temporary table after the operations ended
     * @return array
     */
    protected function computeDropTemporaryTableOperation(): array
    {
        return [
            Data::FETCH_DATA_TABLE => $this->computeTemporaryTableName(),
        ];
    }

    /**
     * Function used to compute the operations needed to retrieve data from Reporting tables
     * @return array
     */
    protected function computeFetchDataFromReportingTableOperations(): array
    {
        $fetchOperations = [];

        /* Add flag to determine if data should be inserted in a temporary table or retrieved */
        $fetchDataMode = $this->fetchFromMultipleTables ? Data::FETCH_DATA_MODE_INSERT : Data::FETCH_DATA_MODE_SELECT;

        foreach ($this->tablesAndIntervals as $table => $intervals) {
            $fetchOperation = [
                Data::FETCH_DATA_TABLE => $table,
                Data::FETCH_DATA_COLUMNS => $this->computeColumns(
                    $this->fetchData[Data::COLUMNS],
                    $intervals,
                    $this->fetchFromMultipleTables
                ),
                Data::FETCH_DATA_WHERE_CLAUSE => $this->computeQueryWhereClause(),
                Data::FETCH_DATA_GROUP_CLAUSE => $this->computeQueryGroupClause(),
            ];

            $fetchOperations[] = $fetchOperation;
        }

        return [
            Data::FETCH_DATA_MODE => $fetchDataMode,
            Data::FETCH_DATA => $fetchOperations,
        ];
    }


    /**
     * Short function used to compute and return the temporary table name
     * @return string
     */
    protected function computeTemporaryTableName(): string
    {
        if (is_null($this->temporaryTableName)) {
            $this->temporaryTableName = uniqid(Data::TEMPORARY_TABLE_NAME_TEMPLATE);
        }

        return $this->temporaryTableName;
    }

    /**
     * Function used to compute the query where clause
     * @return array
     */
    protected function computeQueryWhereClause(): array
    {
        return $this->fetchData[Data::WHERE_CLAUSE];
    }

    /**
     * Function used to compute the query groupBy clause
     * @return array
     */
    protected function computeQueryGroupClause(): array
    {
        return $this->fetchData[Data::GROUP_CLAUSE];
    }

    /**
     * Function used to alter the columns array in order to simulate data fetching from a second temporary table
     * @param array $columns
     * @return array
     */
    protected function computeFetchTemporaryColumns(array $columns): array
    {
        return array_map(function ($column) {
            $column[Data::COLUMN_NAME] = $column[Data::COLUMN_ALIAS];

            return $column;
        }, $columns);
    }

    /**
     * Function used to compute columns
     * @param array $columns
     * @param array $intervals
     * @param bool $compactAggregateColumns
     * @param bool $parseQueryExtra
     * @return array
     */
    protected function computeColumns(
        array $columns,
        array $intervals,
        bool $compactAggregateColumns = false,
        bool $parseQueryExtra = false
    ): array {
        /* Add group columns to select columns */
        $groupColumns = array_combine(
            $this->fetchData[Data::GROUP_CLAUSE],
            $this->fetchData[Data::GROUP_CLAUSE]
        );

        /* Add columns computed from extracting the relevant JSON keys from interval columns */
        $aggregateColumns = [];
        foreach ($columns as $column) {
            list($columnAlias, $columnSyntax) = $this->mapIntervalsToQueryFunctions(
                $column,
                $intervals,
                $parseQueryExtra
            );

            $aggregateColumns[$columnAlias] = $columnSyntax;
        };

        if ($compactAggregateColumns) {
            $aggregateColumns = $this->transformToJsonObject($aggregateColumns);
        }

        return array_merge(
            $groupColumns,
            $aggregateColumns
        );
    }

    /**
     * Function used to compact columns into a MySQL JSON Object
     * @param $columns
     * @return array
     */
    protected function transformToJsonObject($columns): array
    {
        $computedColumns = [];

        foreach ($columns as $columnName => $syntax) {
            $computedColumns[] = Utils::quote($columnName);
            $computedColumns[] = $syntax;
        }

        return [
            Data::TEMPORARY_TABLE_AGGREGATE_COLUMN_NAME => sprintf(
                Functions::JSON_OBJECT_FUNCTION,
                implode(', ', $computedColumns)
            ),
        ];
    }
}