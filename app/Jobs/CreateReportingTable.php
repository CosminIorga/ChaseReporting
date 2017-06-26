<?php

namespace App\Jobs;

use App\Definitions\Data;
use App\Definitions\Table;
use App\Exceptions\CreateTableException;
use App\Factories\ReportingTableFactory;
use App\Models\ColumnModel;
use App\Models\ReportingTables\ReportingTable;
use App\Models\ResponseModel;
use App\Repositories\DataRepository;
use App\Services\ConfigGetter;
use App\Traits\CustomConsoleOutput;
use App\Traits\LogHelper;
use App\Transformers\TransformConfigColumn;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class CreateReportingTable extends DefaultJob
{
    use CustomConsoleOutput;
    use LogHelper;

    const CONNECTION = "sync";
    const QUEUE_NAME = "createReportingTable";

    /**
     * Global getter used to get config values and validate them in advance
     * @var ConfigGetter
     */
    protected $configGetter;

    /**
     * Data Repository
     * @var DataRepository
     */
    protected $dataRepository;

    /**
     * Variable used to know which reporting table it should create
     * @var Carbon
     */
    protected $referenceDate;

    /**
     * The interval each table stores
     * @var string
     */
    protected $tableInterval;

    /**
     * The interval each data record stores
     * @var string
     */
    protected $dataInterval;

    /**
     * Class used to handle the specific table interval data
     * @var ReportingTable
     */
    protected $reportingTableModel;

    /**
     * The table name
     * @var string
     */
    protected $tableName;

    /**
     * Variable used to store the table columns
     * @var Collection
     */
    protected $tableStructure;

    /**
     * CreateReportingTable constructor.
     * @param Carbon $referenceDate
     */
    public function __construct(Carbon $referenceDate)
    {
        $this->init($referenceDate);

        $this->setChannel('create-table');
    }

    /**
     * Function used to initialize various fields
     * @param Carbon $referenceDate
     */
    protected function init(Carbon $referenceDate)
    {
        /* Initialize config getter */
        $this->configGetter = ConfigGetter::Instance();
        /* Save reference date */
        $this->referenceDate = $referenceDate;

        /* Retrieve tableInterval and dateInterval */
        $this->tableInterval = $this->configGetter->tableInterval;
        $this->dataInterval = $this->configGetter->dataInterval;
    }


    public function handle(
        DataRepository $dataRepository
    ): ResponseModel {
        /* Save repository variable as class-wide variable */
        $this->dataRepository = $dataRepository;

        /* Get class that handles the config table interval */
        $this->getReportingTableModel();

        /* Get table name */
        $this->getTableName();

        /* Check if table exists */
        $this->checkIfTableExists();

        /* Compute table structure */
        $this->computeTableStructure();

        /* Create table */
        $this->createTable();

        /* Set return response */
        $this->setResponse(true, Table::MESSAGE_TABLE_CREATED_SUCCESSFULLY);

        return $this->getResponse();
    }

    /**
     * Function used to retrieve the class which handles the given table interval
     */
    protected function getReportingTableModel()
    {
        /* Get the reportingTableModel */
        $reportingTableModel = ReportingTableFactory::build($this->tableInterval);

        /* Init the model*/
        $reportingTableModel->init($this->referenceDate, $this->dataInterval);

        $this->reportingTableModel = $reportingTableModel;
    }

    /**
     * Function used to retrieve the table name using the handler class
     */
    protected function getTableName()
    {
        $this->tableName = $this->reportingTableModel->getTableName();
    }

    /**
     * Check if table exists. Throw exception if so
     */
    protected function checkIfTableExists()
    {
        $exists = $this->dataRepository->tableExists($this->tableName);

        if ($exists) {
            throw new CreateTableException(
                sprintf(
                    CreateTableException::TABLE_ALREADY_EXISTS,
                    $this->tableName
                )
            );
        }
    }

    /**
     * Function used to compute table columns
     */
    protected function computeTableStructure()
    {
        $primaryColumn = $this->computePrimaryColumn();
        $pivotColumns = $this->computePivotColumns();

        $intervalColumns = $this->computeIntervalColumns();

        $this->tableStructure = collect([])
            ->merge($primaryColumn)
            ->merge($pivotColumns)
            ->merge($intervalColumns);
    }

    /**
     * Function used to compute the primary column
     * @return Collection
     */
    protected function computePrimaryColumn(): Collection
    {
        $data = $this->configGetter->primaryColumnData;

        $transformedData = (new TransformConfigColumn())->toColumnModelData($data);
        $column = new ColumnModel($transformedData);

        return collect([
            $column
        ]);
    }

    /**
     * Function used to compute the pivot columns
     * @return Collection
     */
    protected function computePivotColumns(): Collection
    {
        $data = $this->configGetter->pivotColumnsData;

        $columns = collect([]);

        array_walk($data, function ($record) use ($columns) {
            $transformedData = (new TransformConfigColumn())->toColumnModelData($record);
            $column = new ColumnModel($transformedData);

            $columns->push($column);
        });

        return $columns;
    }

    /**
     * Function used to compute the interval columns
     * @return Collection
     */
    protected function computeIntervalColumns(): Collection
    {
        $columnCount = $this->reportingTableModel->getIntervalColumnCount();
        $range = range(1, $columnCount);
        $data = collect([]);
        $configData = $this->configGetter->intervalColumnData;

        array_walk($range, function ($columnIndex) use ($data, $configData) {
            $configData[Data::CONFIG_COLUMN_NAME] = $this->reportingTableModel->getIntervalColumnByIndex($columnIndex);

            $transformedData = (new TransformConfigColumn())->toColumnModelData($configData);
            $column = new ColumnModel($transformedData);

            $data->push($column);
        });

        return $data;
    }

    /**
     * Function used to create table
     * @throws CreateTableException
     */
    protected function createTable()
    {
        /* Set table name we wish to act upon */
        $this->dataRepository->setTable($this->tableName);

        /* Create the table */
        list($success, $message) = $this->dataRepository->createTable($this->tableStructure);

        if (!$success) {
            throw new CreateTableException(
                sprintf(
                    CreateTableException::TABLE_FAILED_TO_CREATE,
                    $this->tableName,
                    $message ?? CreateTableException::UNKNOWN_REASON
                )
            );
        }
    }
}
