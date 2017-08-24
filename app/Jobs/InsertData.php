<?php

namespace App\Jobs;


use App\Definitions\Columns;
use App\Definitions\Data;
use App\Exceptions\InsertDataException;
use App\Factories\ReportingTableFactory;
use App\Models\ReportingTables\ReportingTable;
use App\Repositories\DataRepository;
use App\Services\ConfigGetter;
use App\Traits\InputFunctions;
use App\Transformers\TransformInsertData;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class InsertData extends DefaultJob
{
    use InputFunctions;

    const CONNECTION = "sync";
    const QUEUE_NAME = "insertTheData";

    /**
     * An array of arrays containing raw data to be processed and inserted into reporting table
     * @var array
     */
    protected $data;

    /**
     * The Data Repository
     * @var DataRepository
     */
    protected $dataRepository;

    /**
     * The Config Getter
     * @var ConfigGetter
     */
    protected $configGetter;

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
     * Validate data to match format
     */
    protected function validateInput()
    {
        /* Check if array is empty */
        if (empty($this->data)) {
            throw new InsertDataException(InsertDataException::DATA_IS_EMPTY);
        }

        $requiredColumns = $this->computeRequiredColumns();

        /* Iterate through data array */
        array_walk($this->data, function (array $record) use ($requiredColumns) {
            /* Check each record contains required columns such as pivots and timestamp column */
            $requiredColumns->each(function ($requiredColumn) use ($record) {
                if (!array_key_exists($requiredColumn, $record)) {
                    throw new InsertDataException(
                        sprintf(
                            InsertDataException::INCOMPLETE_RECORD,
                            $requiredColumn
                        )
                    );
                }
            });
        });
    }

    /**
     * Function used to compute the required columns such as timestamp column and pivot columns
     * @return Collection
     */
    protected function computeRequiredColumns(): Collection
    {
        $columnMapping = $this->configGetter->getColumnMapping();

        /* Filter columns that are not timestamp or pivot */
        $filteredColumns = array_filter($columnMapping, function ($columnInfo) {
            return
                ($columnInfo[Data::CONFIG_COLUMN_TYPE] == Columns::COLUMN_PIVOT) ||
                ($columnInfo[Data::CONFIG_COLUMN_TYPE] == Columns::COLUMN_TIMESTAMP);
        });

        return collect(
            array_column($filteredColumns, Data::CONFIG_COLUMN_NAME)
        );
    }

    /**
     * Job runner
     * @param DataRepository $dataRepository
     */
    public function handle(
        DataRepository $dataRepository
    ) {
        $this->dataRepository = $dataRepository;

        $transformedData = [];

        /* Transform data */
        array_walk($this->data, function (array $record) use (&$transformedData) {
            /* Get the reportingTableModel */
            $reportingTableModel = $this->getReportingTableModel($record);

            /* Compute table based on dataInterval and reference date */
            $tableName = $reportingTableModel->getTableName();

            /* Transform current record */
            list($hashColumn, $pivotColumns, $intervalColumns) =
                (new TransformInsertData())->toReportingData($record, $reportingTableModel);

            /* Compute key based on hashColumn and tableName */
            $key = current($hashColumn) . "___" . $tableName;

            if (!array_key_exists($key, $transformedData)) {
                $transformedData[$key] = [
                    Data::INSERT_RECORD_PRIMARY_KEY_VALUE => $hashColumn,
                    Data::INSERT_RECORD_TABLE_NAME => $tableName,
                    Data::INSERT_RECORD_FIXED_DATA => array_merge($hashColumn, $pivotColumns),
                    Data::INSERT_RECORD_VOLATILE_DATA => [],
                ];
            }

            $transformedData[$key][Data::INSERT_RECORD_VOLATILE_DATA] = array_merge_recursive(
                $transformedData[$key][Data::INSERT_RECORD_VOLATILE_DATA],
                $intervalColumns
            );
        });

        /* Create or update records with given data */
        array_walk($transformedData, function (array $record) {
            /* Set repository table */
            $this->dataRepository->setTable($record[Data::INSERT_RECORD_TABLE_NAME]);

            /* Check if record exists for given hash */
            $hashValue = current($record[Data::INSERT_RECORD_PRIMARY_KEY_VALUE]);

            list($recordExists, $currentRecord) = $this->checkAndReturnIfRecordExists($hashValue);

            /* Merge current data with to-be-inserted data */
            $currentIntervalColumns = array_intersect_key($currentRecord, $record[Data::INSERT_RECORD_VOLATILE_DATA]);

            $mergedIntervalColumns = $this->mergeRecords(
                array_merge_recursive(
                    $record[Data::INSERT_RECORD_VOLATILE_DATA],
                    $currentIntervalColumns
                )
            );

            /* Create new record if record does not exists */
            if (!$recordExists) {
                $newRecord = array_merge(
                    $record[Data::INSERT_RECORD_FIXED_DATA],
                    $mergedIntervalColumns
                );

                $this->dataRepository->insert($newRecord);

                return;
            }

            $this->dataRepository->update($mergedIntervalColumns, [
                current(array_keys($record[Data::INSERT_RECORD_PRIMARY_KEY_VALUE])) => $hashValue,
            ]);
        });
    }

    /**
     * Function used to return an array if necessary information
     * @param array $record
     * @return ReportingTable
     */
    protected function getReportingTableModel(array $record): ReportingTable
    {
        /* Get timestamp data */
        $timestampData = $this->configGetter->timestampData;
        /* Get config tableInterval */
        $tableInterval = $this->configGetter->tableInterval;
        /* Get config dataInterval */
        $dataInterval = $this->configGetter->dataInterval;

        $referenceDate = new Carbon($record[$timestampData[Data::CONFIG_COLUMN_NAME]]);

        /* Get class that handles the current tableInterval */
        $reportingTableModel = ReportingTableFactory::build($tableInterval);
        $reportingTableModel->init($referenceDate, $dataInterval);

        return $reportingTableModel;
    }

    /**
     * Function used to check if record exists given a string hash
     * @param string $hash
     * @return array
     */
    protected function checkAndReturnIfRecordExists(string $hash): array
    {
        $data = $this->dataRepository->findByHash($hash);

        return [
            !empty($data),
            $data,
        ];
    }

    /**
     * Function used to merge multiple records
     * @param array $intervals
     * @return array
     */
    protected function mergeRecords(array $intervals): array
    {
        $mergedData = [];

        foreach ($intervals as $intervalKey => $intervalValues) {
            if (!is_array($intervalValues)) {
                $intervalValues = [
                    $intervalValues,
                ];
            }

            $intervalValues = array_map(function (string $intervalValue) {
                return json_decode($intervalValue, true);
            }, array_filter($intervalValues));

            $intervalValues = call_user_func_array('array_merge_recursive', $intervalValues);

            $aggregatedJson = [];
            foreach ($intervalValues as $jsonKey => $jsonValues) {
                /* Get the function associated to the jsonKey */
                $aggregateConfig = $this->configGetter->getAggregateConfigByJsonName($jsonKey);

                /* Convert string and int values to array */
                if (!is_array($jsonValues)) {
                    $jsonValues = [
                        $jsonValues,
                    ];
                }

                /* Aggregate the values */
                $aggregatedJson[$jsonKey] = $this->aggregateValues($jsonValues, $aggregateConfig);
            }

            $mergedData[$intervalKey] = json_encode($aggregatedJson);
        }

        return $mergedData;
    }

}
