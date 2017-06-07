<?php
/**
 * Created by PhpStorm.
 * User: chase
 * Date: 17/05/17
 * Time: 16:36
 */

namespace App\Jobs;


use App\Definitions\Data;
use App\Exceptions\FetchDataException;
use App\Factories\ReportingTableFactory;
use App\Models\ReportingTables\ReportingTable;
use App\Repositories\DataRepository;
use App\Services\ConfigGetter;
use App\Traits\Common;
use App\Traits\CustomConsoleOutput;
use App\Traits\Functions;
use App\Transformers\TransformFetchData;
use Carbon\Carbon;
use DateInterval;
use DatePeriod;
use Illuminate\Support\Collection;

class FetchData extends DefaultJob
{
    use CustomConsoleOutput;
    use Functions;
    use Common;

    const CONNECTION = "sync";
    const QUEUE_NAME = "insertTheData";

    /**
     * An array containing necessary information used to fetch data from the reporting system
     * @var array
     */
    protected $data;

    /**
     * The Config Getter
     * @var ConfigGetter
     */
    protected $configGetter;

    /**
     * The Data Repository
     * @var DataRepository
     */
    protected $dataRepository;

    /**
     * InsertData constructor.
     * @param array $data
     */
    public function __construct(array $data)
    {
        $this->init($data);
    }

    /**
     * Function used to initialize various fields
     * @param array $data
     */
    protected function init(array $data)
    {
        $this->data = $data;
        $this->configGetter = ConfigGetter::Instance();

        $this->validateInput();
    }

    /**
     * Validate input data to contain minimum necessary information
     */
    protected function validateInput()
    {
        /* Check if array is empty */
        if (empty($this->data)) {
            throw new FetchDataException(FetchDataException::DATA_IS_EMPTY);
        }

        /* Check if array contains necessary keys */
        $requiredKeys = [
            Data::FETCH_INTERVAL_START,
            Data::FETCH_INTERVAL_END,
            Data::FETCH_COLUMNS,
            Data::FETCH_GROUP_CLAUSE,
            Data::FETCH_WHERE_CLAUSE,
            Data::FETCH_ORDER_CLAUSE
        ];

        foreach ($requiredKeys as $requiredKey) {
            if (!array_key_exists($requiredKey, $this->data)) {
                throw new FetchDataException(
                    sprintf(
                        FetchDataException::MISSING_KEY,
                        $requiredKey
                    )
                );
            }
        }

        /* Check if columns key contains only values defined in aggregates config array */
        $aggregates = $this->configGetter->aggregateData;
        $aggregateNames = array_column($aggregates, Data::AGGREGATE_JSON_NAME);

        foreach ($this->data[Data::FETCH_COLUMNS] as $column) {
            if (!in_array($column, $aggregateNames)) {
                throw new FetchDataException(
                    sprintf(
                        FetchDataException::INVALID_COLUMN_VALUE,
                        $column
                    )
                );
            }
        }

        /* Check if groupClause key contains only values from the pivot config array */
        $pivots = $this->configGetter->pivotColumnsData;
        $pivotNames = array_column($pivots, Data::CONFIG_COLUMN_NAME);

        foreach ($this->data[Data::FETCH_GROUP_CLAUSE] as $pivot) {
            if (!in_array($pivot, $pivotNames)) {
                throw new FetchDataException(
                    sprintf(
                        FetchDataException::INVALID_PIVOT_VALUE,
                        $pivot
                    )
                );
            }
        }

        //TODO: further validate group clause and order clause
    }

    /**
     * Job runner
     * @param DataRepository $dataRepository
     * @return array
     */
    public function handle(
        DataRepository $dataRepository
    ): array {
        $this->dataRepository = $dataRepository;

        /* Compute necessary tables and interval columns */
        $tablesAndColumns = $this->fetchAndMonitor('fetchTablesAndColumns');

        /* Transform received data, tables and columns into queryData */
        $queryData = $this->fetchAndMonitor('transformData', $tablesAndColumns);

        /* Retrieve results using given queryData */
        $results = $this->fetchAndMonitor('fetchData', $queryData);

        /* Process results */
        $processedResults = $this->fetchAndMonitor('processResults', $results);

        /* Order results */
        $orderedResults = $this->fetchAndMonitor('orderResults', $processedResults);

        return $orderedResults;
    }

    /**
     * Function created for debugging purposes to measure performance of each function
     * @param string $function
     * @param array ...$params
     * @return mixed
     */
    protected function fetchAndMonitor(string $function, ... $params)
    {
        /* Start timer for performance benchmarks */
        $startTime = microtime(true);

        $response = call_user_func_array([$this, $function], $params);

        /* Compute total operations time */
        $endTime = microtime(true);
        $elapsed = $endTime - $startTime;

        echo "Function $function took: $elapsed seconds" . PHP_EOL;

        return $response;
    }

    /**
     * Function used to retrieve a list of tables and associated columns from which to fetch the data
     */
    protected function fetchTablesAndColumns()
    {
        /* Get data interval from config */
        $dataInterval = $this->configGetter->dataInterval;

        /* Compute necessary information to retrieve periods between startDate and endDate with a step of dataInterval */
        $startDate = new Carbon($this->data[Data::FETCH_INTERVAL_START]);
        $endDate = new Carbon($this->data[Data::FETCH_INTERVAL_END]);
        $dateInterval = new DateInterval("PT{$dataInterval}M");

        /* Compute periods */
        $periods = new DatePeriod($startDate, $dateInterval, $endDate);

        /* Compute reporting table model */
        $reportingTableModel = $this->getReportingTableModel($startDate);

        $tablesAndColumns = [];

        foreach ($periods as $referenceDate) {
            $reportingTableModel->setReferenceDate($referenceDate);
            $tableName = $reportingTableModel->getTableName();

            $intervalColumn = $reportingTableModel->getIntervalColumnByReferenceData($referenceDate);

            $tablesAndColumns[$tableName][] = $intervalColumn;
        }

        return $tablesAndColumns;
    }

    /**
     * Function used to compute reportingTableModel based on given reference date
     * @param Carbon $referenceDate
     * @return ReportingTable
     */
    protected function getReportingTableModel(Carbon $referenceDate): ReportingTable
    {
        /* Get config tableInterval */
        $tableInterval = $this->configGetter->tableInterval;
        /* Get config dataInterval */
        $dataInterval = $this->configGetter->dataInterval;

        /* Get class that handles the current tableInterval */
        $reportingTableModel = ReportingTableFactory::build($tableInterval);
        $reportingTableModel->init($referenceDate, $dataInterval);

        return $reportingTableModel;
    }

    /**
     * Function used to transform data
     * @param array $tablesAndColumns
     * @return array
     */
    protected function transformData(array $tablesAndColumns): array
    {
        $queryData = (new TransformFetchData())->toReportingData($this->data, $tablesAndColumns);

        return $queryData;
    }

    /**
     * Function used to fetch data
     * @param array $queryData
     * @return Collection
     */
    protected function fetchData(array $queryData): Collection
    {
        $results = $this->dataRepository->fetchData($queryData);

        return $results;
    }

    /**
     * Function used to process database results and further aggregate the data
     * @param Collection $queryResults
     * @return array
     */
    protected function processResults(Collection $queryResults): array
    {
        $endData = [];

        foreach ($queryResults as $queryRecord) {
            $hash = $queryRecord->{Data::HASH_COLUMN_ALIAS};

            /* Add pivots */
            if (!array_key_exists($hash, $endData)) {
                $endData[$hash] = (array)$queryRecord;
                $endData[$hash][Data::DATA_COLUMN_ALIAS] = [];

                unset($endData[$hash][Data::HASH_COLUMN_ALIAS]);
            }

            /* Explode pre-merged data */
            $jsonData = explode(Data::CONCAT_SEPARATOR, $queryRecord->{Data::DATA_COLUMN_ALIAS});

            /* Iterate through JSON records */
            foreach ($jsonData as $jsonRecord) {
                $endData[$hash][Data::DATA_COLUMN_ALIAS] = array_merge_recursive(
                    $endData[$hash][Data::DATA_COLUMN_ALIAS],
                    json_decode($jsonRecord, true)
                );
            }
        }

        $endData = array_map(function (array $record) {
            foreach ($record[Data::DATA_COLUMN_ALIAS] as $key => &$value) {
                if (!in_array($key, $this->data[Data::FETCH_COLUMNS])) {
                    unset ($record[Data::DATA_COLUMN_ALIAS][$key]);
                }

                if (is_array($value)) {
                    $aggregateConfig = $this->configGetter->getAggregateConfigByJsonName($key);
                    $value = $this->aggregateValues($value, $aggregateConfig);
                }
            }

            return $record;
        }, $endData);

        return array_values($endData);
    }

    /**
     * Function used to order to processed results
     * @param array $processedResults
     * @return array
     */
    protected function orderResults(array $processedResults): array
    {
        $order = $this->data[Data::FETCH_ORDER_CLAUSE];

        /* Flatten arrays before ordering */
        $processedResults = array_map(function ($record) {
            return $this->flattenArray($record);
        }, $processedResults);

        /* Order arrays */
        usort($processedResults, function (array $record1, array $record2) use ($order) {
            foreach ($order as $orderClause) {
                $orderKey = $orderClause[0];
                $orderDir = $orderClause[1];

                if ($record1[$orderKey] == $record2[$orderKey]) {
                    continue;
                }

                if ($orderDir == 'ASC') {
                    return $record1[$orderKey] > $record2[$orderKey];
                }

                return $record1[$orderKey] < $record2[$orderKey];
            }

            return 0;
        });


        return $processedResults;
    }
}