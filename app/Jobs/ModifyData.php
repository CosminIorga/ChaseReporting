<?php

namespace App\Jobs;


use App\Definitions\Columns;
use App\Definitions\Data;
use App\Exceptions\ModifyDataException;
use App\Factories\ReportingTableFactory;
use App\Models\ReportingTables\ReportingTable;
use App\Repositories\DataRepository;
use App\Services\ConfigGetter;
use App\Traits\InputFunctions;
use App\Transformers\TransformInsertData;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class ModifyData extends DefaultJob
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
     * Variable used to store the current operation to be executed
     * @var string
     */
    private $operation;

    /**
     * InsertData constructor.
     * @param string $operation
     * @param array $data
     */
    public function __construct(string $operation, array $data)
    {
        $this->init($operation, $data);
    }

    /**
     * Function used to initialize various fields
     * @param string $operation
     * @param array $data
     */
    protected function init(string $operation, array $data)
    {
        $this->data = $data;
        $this->operation = $operation;

        $this->configGetter = ConfigGetter::Instance();

        $this->validateInput();
    }

    /**
     * Validate data to match format
     */
    protected function validateInput()
    {
        /* Check if operation is allowed */
        if (!in_array($this->operation, Data::ALLOWED_MODIFY_DATA_OPERATIONS)) {
            throw new ModifyDataException(sprintf(
                ModifyDataException::INVALID_MODIFY_DATA_OPERATION,
                $this->operation
            ));
        }

        /* Check if array is empty */
        if (empty($this->data)) {
            throw new ModifyDataException(ModifyDataException::DATA_IS_EMPTY);
        }

        $requiredColumns = $this->computeRequiredColumns();

        /* Iterate through data array */
        array_walk($this->data, function (array $record) use ($requiredColumns) {
            /* Check each record contains required columns such as pivots and timestamp column */
            $requiredColumns->each(function ($requiredColumn) use ($record) {
                if (!array_key_exists($requiredColumn, $record)) {
                    throw new ModifyDataException(
                        sprintf(
                            ModifyDataException::INCOMPLETE_RECORD,
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
        $columnMapping = $this->configGetter->columnMapping;

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

        $data = [];

        /* Transform data */
        array_walk($this->data, function (array $record) use (&$data) {
            /* Get the reportingTableModel */
            $reportingTableModel = $this->getReportingTableModel($record);

            /* Compute table based on dataInterval and reference date */
            $tableName = $reportingTableModel->getTableName();

            /* Transform current record */
            list($hashColumn, $pivotColumns, $intervalColumns) =
                (new TransformInsertData())->toReportingData($this->operation, $record, $reportingTableModel);

            /* Compute key based on hashColumn and tableName */
            $key = current($hashColumn) . "___" . $tableName;

            if (!array_key_exists($key, $data)) {
                $data[$key] = [
                    Data::INSERT_RECORD_PRIMARY_KEY_VALUE => $hashColumn,
                    Data::INSERT_RECORD_TABLE_NAME => $tableName,
                    Data::INSERT_RECORD_FIXED_COLUMNS => array_merge($hashColumn, $pivotColumns),
                    Data::INSERT_RECORD_AGGREGATE_COLUMNS => [],
                ];
            }

            $data[$key][Data::INSERT_RECORD_AGGREGATE_COLUMNS] = array_merge_recursive(
                $data[$key][Data::INSERT_RECORD_AGGREGATE_COLUMNS],
                $intervalColumns
            );
        });

        /* Create, update or delete records based on transformed data */
        array_walk($data, function (array $newRecord) {
            /* Set repository table */
            $this->dataRepository->setTable($newRecord[Data::INSERT_RECORD_TABLE_NAME]);

            /* Check if record already exists for given hash */
            $hashValue = current($newRecord[Data::INSERT_RECORD_PRIMARY_KEY_VALUE]);

            list($recordExists, $currentRecord) = $this->checkAndReturnIfRecordExists($hashValue);

            /* Merge current interval columns with computed interval columns */
            $mergedAggregateColumns = $this->mergeRecords(
                array_merge_recursive(
                    $newRecord[Data::INSERT_RECORD_AGGREGATE_COLUMNS],
                    array_intersect_key($currentRecord, $newRecord[Data::INSERT_RECORD_AGGREGATE_COLUMNS])
                )
            );

            /* Check if all interval columns are empty */
            $deleteRecord = $this->checkIfNewRecordIsEmpty(
                $mergedAggregateColumns,
                array_diff_key($currentRecord, $newRecord[Data::INSERT_RECORD_FIXED_COLUMNS])
            );

            $newRecord[Data::INSERT_RECORD_AGGREGATE_COLUMNS] = $mergedAggregateColumns;

            $this->executeOperation($recordExists, $deleteRecord, $newRecord);
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
     * @param array $newIntervalColumns
     * @return array
     */
    protected function mergeRecords(array $newIntervalColumns): array
    {
        $mergedData = [];

        foreach ($newIntervalColumns as $intervalKey => $intervalValues) {
            if (!is_array($intervalValues)) {
                $intervalValues = [
                    $intervalValues,
                ];
            }

            $intervalValues = array_filter(
                array_map(
                    function (string $intervalValue) {
                        return json_decode($intervalValue, true);
                    },
                    array_filter($intervalValues)
                ),
                'is_array'
            );

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
                $aggregateValue = $this->aggregateValues($jsonValues, $aggregateConfig);
                $aggregatedJson[$jsonKey] = $aggregateValue;
            }

            /* Nullify data is record count is 0. */
            $aggregatedJson = ($aggregatedJson[Columns::META_RECORD_COUNT] == 0) ? null : json_encode($aggregatedJson);

            $mergedData[$intervalKey] = $aggregatedJson;
        }

        return $mergedData;
    }

    /**
     * Function used to check if all interval columns are NULL and mark record as to be deleted
     * @param array $newIntervalColumns
     * @param array $currentIntervalColumns
     * @return bool
     */
    protected function checkIfNewRecordIsEmpty(array $newIntervalColumns, array $currentIntervalColumns): bool
    {
        $mergedIntervalColumns = array_replace(
            $currentIntervalColumns,
            $newIntervalColumns
        );

        /* Iterate over merged columns and check if any column is not null */
        foreach ($mergedIntervalColumns as $value) {
            if (!is_null($value)) {
                return false;
            }
        }

        /* Otherwise mark record as to be deleted */

        return true;
    }

    /**
     * Function used to execute the operation
     * @param bool $recordExists
     * @param bool $deleteRecord
     * @param array $record
     * @throws ModifyDataException
     */
    protected function executeOperation(
        bool $recordExists,
        bool $deleteRecord,
        array $record
    ) {
        /* Switch between operations */
        switch ($this->operation) {
            case Data::MODIFY_DATA_OPERATION_INSERT:
                /* Create new record if record does not exists */
                if (!$recordExists) {
                    $toInsertRecord = array_merge(
                        $record[Data::INSERT_RECORD_FIXED_COLUMNS],
                        $record[Data::INSERT_RECORD_AGGREGATE_COLUMNS]
                    );

                    $this->dataRepository->insert($toInsertRecord);

                    return;
                }

                /* Update record in both cases */
                $this->dataRepository->update(
                    $record[Data::INSERT_RECORD_AGGREGATE_COLUMNS],
                    $record[Data::INSERT_RECORD_PRIMARY_KEY_VALUE]
                );

                return;
            case Data::MODIFY_DATA_OPERATION_DELETE:
                /* Check if record exists. Throw error if it doesn't */
                if (!$recordExists) {
                    throw new ModifyDataException(ModifyDataException::RECORD_NOT_FOUND);
                }

                /* Delete record if flag is set to false */
                if ($deleteRecord) {
                    $this->dataRepository->delete($record[Data::INSERT_RECORD_PRIMARY_KEY_VALUE]);

                    return;
                }

                /* Update record in both cases */
                $this->dataRepository->update(
                    $record[Data::INSERT_RECORD_AGGREGATE_COLUMNS],
                    $record[Data::INSERT_RECORD_PRIMARY_KEY_VALUE]
                );

                return;
            default:
                /* It won't reach this branch due to validation */
                break;
        }
    }
}
