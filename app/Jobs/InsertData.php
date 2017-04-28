<?php

namespace App\Jobs;


use App\Exceptions\InsertDataException;
use App\Factories\ReportingTableFactory;
use App\Models\InsertDataModel;
use App\Models\ReportingTables\ReportingTable;

class InsertData extends DefaultJob
{
    const CONNECTION = "sync";
    const QUEUE_NAME = "insertData";

    /**
     * An array of arrays containing raw data to be processed and inserted into reporting table
     * @var array
     */
    private $data;

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

        $this->validateInput();
    }

    /**
     * Validate data to match format
     */
    protected function validateInput()
    {
        /* Check if received data is array */
        if (!is_array($this->data)) {
            throw new InsertDataException(InsertDataException::INVALID_DATA_FORMAT);
        }

        /* Check if array is empty */
        if (empty($this->data)) {
            throw new InsertDataException(InsertDataException::DATA_IS_EMPTY);
        }

        /* Check if each subsequent array element must be an array */
        array_walk($this->data, function ($record) {
            if (!is_array($record)) {
                throw new InsertDataException(InsertDataException::RECORD_IS_NOT_ARRAY);
            }
        });
    }

    /**
     * Job runner
     */
    protected function handle()
    {
        /* Get necessary information such as table name */
        list($tableName) = $this->getNecessaryInformation();

        /* Transform received data to match the table's column structure */

        /* Check if record already exists for given pivot data */


        /* If record exists */
            /* Insert new record */

        /* Otherwise */
            /* Aggregate data with already existent record */

            /* Update record */

    }

    protected function getNecessaryInformation()
    {
        /* Get the config tableInterval and dataInterval */
        $tableInterval = config('common.table_interval');
        $dataInterval = config('common.data_interval');

        /** @var ReportingTable $reportingTableModel */
        $reportingTableModel = ReportingTableFactory::build($tableInterval);
        $reportingTableModel->init(null, $dataInterval);

        /* Compute table name */
        $tableName = $reportingTableModel->getTableName();

        return [
            $tableName
        ];
    }

}
