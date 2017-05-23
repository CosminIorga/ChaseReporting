<?php
/**
 * Created by PhpStorm.
 * User: chase
 * Date: 17/05/17
 * Time: 16:36
 */

namespace App\Jobs;


use App\Definitions\Data;
use App\Factories\AggregateFunctionFactory;
use App\Factories\ReportingTableFactory;
use App\Models\ReportingTables\ReportingTable;
use App\Repositories\DataRepository;
use App\Services\ConfigGetter;
use App\Traits\CustomConsoleOutput;
use App\Transformers\TransformFetchData;
use Carbon\Carbon;
use DateInterval;
use DatePeriod;
use Exceptions\FetchDataException;
use Illuminate\Support\Collection;

class FetchData extends DefaultJob
{
    use CustomConsoleOutput;

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

        //TODO: further validate input

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
        $tablesAndColumns = $this->fetchTablesAndColumns();

        /* Transform received data, tables and columns into queryData */
        $queryData = (new TransformFetchData())->toReportingData($this->data, $tablesAndColumns);

        /* Retrieve results using given queryData */
        $results = $this->dataRepository->fetchData($queryData);

        /* Process results */
        $processedResults = $this->processResults($results);

        return $processedResults;
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
     * Function used to process database results and further aggregate the data
     * @param Collection $queryResults
     * @return array
     */
    protected function processResults(Collection $queryResults): array
    {
        $groupColumns = $this->data[Data::FETCH_GROUP_CLAUSE];
        $jsonKeys = $this->data[Data::FETCH_COLUMNS];

        $endData = [];

        $queryResults->each(function (\stdClass $queryRecord) use (&$endData, $groupColumns, $jsonKeys) {
            /* Check if hierarchy of group values exists */
            $groupValues = [];
            foreach ($groupColumns as $groupColumn) {
                $groupValues[$groupColumn] = $queryRecord->{$groupColumn};
            }

            $hash = md5(implode('__', $groupValues));

            /* Check if hash already exists in endData */
            if (!array_key_exists($hash, $endData)) {
                $endData[$hash] = $groupValues;

                /* Instantiate array with values */
                foreach ($jsonKeys as $jsonKey) {
                    $endData[$hash][$jsonKey] = null;
                }
            }

            /* Explode pre-merged data */
            $jsonData = explode(Data::CONCAT_SEPARATOR, $queryRecord->{Data::COLUMN_ALIAS});

            /* Iterate through JSON records */
            foreach ($jsonData as $jsonRecord) {

                /* Decode JSON */
                $jsonRecord = json_decode($jsonRecord, true);

                foreach ($jsonRecord as $jsonKey => $jsonValue) {
                    /* Continue if key does not exist in needed jsonKeys */
                    if (!in_array($jsonKey, $jsonKeys)) {
                        continue;
                    }

                    $aggregateConfig = (ConfigGetter::Instance())->getAggregateConfigByJsonName($jsonKey);

                    $functionModel = AggregateFunctionFactory::build($aggregateConfig[Data::AGGREGATE_FUNCTION]);
                    $functionModel->init($aggregateConfig);

                    /* Check if jsonValue is array */
                    if (!is_array($jsonValue)) {
                        $jsonValue = [$jsonValue];
                    }

                    /* Iterate through jsonValue and aggregate data accordingly */
                    foreach ($jsonValue as $subValue) {
                        if (is_null($endData[$hash][$jsonKey])) {
                            $computedValue = $functionModel->aggregateTwoValues(
                                $subValue
                            );
                        } else {
                            $computedValue = $functionModel->aggregateTwoValues(
                                $subValue,
                                $endData[$hash][$jsonKey]
                            );
                        }

                        $endData[$hash][$jsonKey] = $computedValue;
                    }
                }
            }
        });

        return $endData;
    }
}