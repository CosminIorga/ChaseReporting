<?php

namespace App\Jobs;


use App\Definitions\Columns;
use App\Definitions\Data;
use App\Exceptions\InsertDataException;
use App\Factories\ReportingTableFactory;
use App\Models\ReportingTables\ReportingTable;
use App\Repositories\DataRepository;
use App\Services\ConfigGetter;
use App\Traits\CustomConsoleOutput;
use App\Traits\Functions;
use App\Transformers\TransformInsertData;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class InsertData extends DefaultJob
{
    use CustomConsoleOutput;
    use Functions;

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

        array_walk($this->data, function (array $record) {
            /* Get the reportingTableModel */
            $reportingTableModel = $this->getReportingTableModel($record);

            /* Compute table based on dataInterval and reference date */
            $tableName = $reportingTableModel->getTableName();

            /* Set table name */
            $this->dataRepository->setTable($tableName);

            /* Transform record */
            $transformedRecord = (new TransformInsertData())->toReportingData($record, $reportingTableModel);

            /* Check if record already exists with given hash */
            $primaryKey = $this->configGetter->primaryColumnData;
            $hash = $transformedRecord[$primaryKey[Data::CONFIG_COLUMN_NAME]];

            list($recordExists, $currentRecord) = $this->checkAndReturnIfRecordExists($hash);

            if ($recordExists) {
                /* If record exists then merge current record with database record */
                $finalRecord = $this->mergeRecords($transformedRecord, $currentRecord);

                /* Compute where clause for update*/
                $whereClause = [
                    $primaryKey[Data::CONFIG_COLUMN_NAME] => $hash
                ];

                $this->dataRepository->update($finalRecord, $whereClause);

            } else {
                $finalRecord = $transformedRecord;

                $this->dataRepository->create($finalRecord);
            }
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
            $data
        ];
    }

    /**
     * Function used to merge two records
     * @param array $record
     * @param array $currentRecord
     * @return array
     */
    protected function mergeRecords(array $record, array $currentRecord): array
    {
        $mergedData = array_merge_recursive(array_filter($record, function ($recordValue) {
            return json_decode($recordValue, true) != null;
        }), $currentRecord);

        $finalData = [];
        array_walk($mergedData, function ($record, $key) use (&$finalData) {
            /* Add if merged record is not array */
            if (!is_array($record)) {
                $finalData[$key] = $record;
                return;
            }

            $record = array_merge_recursive(
                json_decode($record[0], true) ?? [],
                json_decode($record[1], true) ?? []
            );

            $tempData = [];
            foreach ($record as $jsonKey => $values) {
                if (!is_array($values)) {
                    $tempData[$jsonKey] = $values;
                    continue;
                }

                $aggregateConfig = $this->configGetter->getAggregateConfigByJsonName($jsonKey);

                $tempData[$jsonKey] = $this->aggregateValues($values, $aggregateConfig);
            }

            $finalData[$key] = json_encode($tempData);
        });

        ksort($finalData);

        return $finalData;
    }

}
