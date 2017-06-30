<?php
/**
 * Created by PhpStorm.
 * User: chase
 * Date: 19/05/17
 * Time: 17:41
 */

namespace App\Transformers;


use App\Definitions\Data;
use App\Definitions\Functions as FunctionsDefinitions;
use App\Services\ConfigGetter;
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
     * The config getter
     * @var ConfigGetter
     */
    protected $configGetter;

    /**
     * Transform fetch data using the tablesAndColumns array to create an array of arrays
     * with necessary information to perform a single query from each sub-array
     * @param array $fetchData
     * @param array $tablesAndColumns
     * @return array
     */
    public function toReportingData(array $fetchData, array $tablesAndColumns): array
    {
        $this->init($fetchData);

        $queryData = [];

        /* Iterate through each table with associated columns */
        foreach ($tablesAndColumns as $table => $columns) {
            /* Instantiate new empty query data array */
            $queryRecord = $this->instantiateEmptyQueryData();

            $queryRecord = array_merge($queryRecord, [
                Data::FETCH_QUERY_DATA_TABLE => $table,
                Data::FETCH_QUERY_DATA_COLUMNS => $this->computeQueryColumns($columns),
                Data::FETCH_QUERY_DATA_WHERE_CLAUSE => $this->computeQueryWhereClause(),
                Data::FETCH_QUERY_DATA_GROUP_CLAUSE => $this->computeQueryGroupClause(),
            ]);

            $queryData[] = $queryRecord;
        }

        return $queryData;
    }

    /**
     * Small function used to initialize fields
     * @param array $fetchData
     */
    protected function init(array $fetchData)
    {
        $this->fetchData = $fetchData;
        $this->configGetter = ConfigGetter::Instance();
    }

    /**
     * Function used to instantiate a new empty query data array
     * @return array
     */
    protected function instantiateEmptyQueryData(): array
    {
        return [
            Data::FETCH_QUERY_DATA_TABLE => '',
            Data::FETCH_QUERY_DATA_COLUMNS => [],
            Data::FETCH_QUERY_DATA_WHERE_CLAUSE => [],
            Data::FETCH_QUERY_DATA_GROUP_CLAUSE => [],
        ];
    }

    /**
     * Function used to compute the query columns
     * @param array $columns
     * @return array
     */
    protected function computeQueryColumns(array $columns): array
    {
        $selectColumns = [];

        /* Add group columns to select columns */
        $selectColumns = array_merge($selectColumns, $this->fetchData[Data::FETCH_GROUP_CLAUSE]);

        /* Add hash column computed from group columns */
        $selectColumns[] = sprintf(
            FunctionsDefinitions::HASH_FUNCTION,
            implode(', ', $this->fetchData[Data::FETCH_GROUP_CLAUSE]),
            Data::HASH_COLUMN_ALIAS
        );

        /* Iterate through fetch columns */
        foreach ($this->fetchData[Data::FETCH_COLUMNS] as $jsonKey => $functions) {
            $aggregateConfig = $this->configGetter->getAggregateConfigByJsonName($jsonKey);

            foreach ($functions as $function => $functionExtra) {
                $selectColumns[] = $this->mapIntervalsToQueryFunctions(
                    $columns,
                    $function,
                    $functionExtra,
                    $aggregateConfig
                );
            }
        }

        return $selectColumns;
    }

    /**
     * Function used to compute the query where clause
     * @return array
     */
    protected function computeQueryWhereClause(): array
    {
        return $this->fetchData[Data::FETCH_WHERE_CLAUSE];
    }

    /**
     * Function used to compute the query groupBy clause
     * @return array
     */
    protected function computeQueryGroupClause(): array
    {
        return $this->fetchData[Data::FETCH_GROUP_CLAUSE];
    }

}