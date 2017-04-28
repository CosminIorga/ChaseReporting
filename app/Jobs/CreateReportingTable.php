<?php

namespace App\Jobs;

use App\Definitions\Columns;
use App\Definitions\Table;
use App\Exceptions\CreateTableException;
use App\Factories\ReportingTableFactory;
use App\Models\ColumnModel;
use App\Models\ReportingTables\ReportingTable;
use App\Models\ResponseModel;
use App\Repositories\Reporting;
use App\Traits\CustomConsoleOutput;
use App\Transformers\TransformConfigPivotColumns;
use DateTime;
use App\Definitions\Common;
use Illuminate\Support\Collection;

class CreateReportingTable extends DefaultJob
{

    use CustomConsoleOutput;

    const CONNECTION = "sync";
    const QUEUE_NAME = "createReportingTable";

    /**
     * Common Repository
     * @var Reporting
     */
    protected $commonRepository;

    /**
     * Variable used to know which reporting table it should create
     * @var DateTime
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
     * @var ReportingTable
     */
    protected $reportingTableModel;


    /**
     * CreateReportingTable constructor.
     * @param DateTime $referenceDate
     */
    public function __construct(DateTime $referenceDate)
    {
        $this->init($referenceDate);
    }

    /**
     * Function used to initialize various fields
     * @param DateTime $referenceDate
     */
    protected function init(DateTime $referenceDate)
    {
        $this->referenceDate = $referenceDate;
        $this->tableInterval = config('common.table_interval');
        $this->dataInterval = config('common.data_interval');

        $this->validateInput();
    }

    /**
     * Function used to validate received and config data
     * @throws CreateTableException
     */
    protected function validateInput()
    {
        /* Check if table interval is valid */
        if (!in_array($this->tableInterval, Common::AVAILABLE_TABLE_INTERVALS)) {
            throw new CreateTableException(
                sprintf(
                    CreateTableException::INVALID_TABLE_INTERVAL,
                    $this->tableInterval
                )
            );
        }

        /* Check if data interval is valid */
        if (!in_array($this->dataInterval, Common::AVAILABLE_DATA_INTERVALS)) {
            throw new CreateTableException(
                sprintf(
                    CreateTableException::INVALID_DATA_INTERVAL,
                    $this->dataInterval
                )
            );
        }

        /* Check if received reference date is of Datetime type */
        if (!$this->referenceDate instanceof DateTime) {
            throw new CreateTableException(
                CreateTableException::INVALID_REFERENCE_DATE
            );
        }
    }

    /**
     * Job runner
     * @param Reporting $commonRepository
     * @return ResponseModel
     */
    public function handle(
        Reporting $commonRepository
    ): ResponseModel {
        /* Save repository variable as class-wide variable */
        $this->commonRepository = $commonRepository;

        /* Get class that contains specific information regarding tableInterval */
        $this->reportingTableModel = $this->getReportingTableJob();

        /* Check if table can be created with given information */
        if (method_exists($this->reportingTableModel, 'checkIfTableCanBeCreated')) {
            $this->reportingTableModel->checkIfTableCanBeCreated();
        }

        /* Get table name */
        $this->tableName = $this->reportingTableModel->getTableName();

        $this->info("Decided following table name: {$this->tableName}");

        /* Check if table already exists */
        $this->checkIfTableExists();

        /* Compute table structure */
        $this->tableStructure = $this->computeTableStructure();

        /* Create table */
        $this->createTable();

        $this->setResponse(true, Table::MESSAGE_TABLE_CREATED_SUCCESSFULLY);

        return $this->getResponse();
    }

    /**
     * Function used to check if table exists
     * @throws CreateTableException
     */
    protected function checkIfTableExists()
    {
        $exists = $this->commonRepository->tableExists($this->tableName);

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
     * Function used to compute table structure
     * @return Collection
     */
    protected function computeTableStructure(): Collection
    {
        $tableStructure = collect([]);

        $primaryColumn = $this->computePrimaryColumn();
        $pivotColumns = $this->computePivotColumns();
        $intervalColumns = $this->computeIntervalColumns();

        return $tableStructure
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
        $primaryColumn = collect([]);

        $primaryColumn->push(new ColumnModel([
            ColumnModel::COLUMN_NAME => Columns::PRIMARY_COLUMN_NAME,
            ColumnModel::COLUMN_TYPE => Columns::COLUMN_IS_PRIMARY,
            ColumnModel::COLUMN_DATA_TYPE => Columns::PRIMARY_COLUMN_DATA_TYPE,
            ColumnModel::COLUMN_INDEX => Columns::PRIMARY_COLUMN_INDEX
        ]));

        return $primaryColumn;
    }

    /**
     * Function used to compute the pivot columns
     * @return Collection
     */
    protected function computePivotColumns(): Collection
    {
        $pivotColumns = collect([]);

        $configPivotColumns = config('columns.pivots');

        array_walk($configPivotColumns, function (array $configPivotColumns) use ($pivotColumns) {
            $columnModelData = (new TransformConfigPivotColumns())->transform($configPivotColumns);

            $columnModelData = array_merge($columnModelData, [
                ColumnModel::COLUMN_TYPE => Columns::COLUMN_IS_PIVOT
            ]);

            $columnModel = new ColumnModel($columnModelData);

            $pivotColumns->push($columnModel);
        });

        return $pivotColumns;
    }

    /**
     * Function used to compute table structure
     * @return Collection
     */
    protected function computeIntervalColumns(): Collection
    {

        /* Compute the number of columns based on data interval and table interval */
        $columnCount = $this->reportingTableModel->getIntervalColumnCount();

        $columns = collect([]);

        foreach (range(1, $columnCount) as $columnIndex) {
            $columnModel = new ColumnModel([
                ColumnModel::COLUMN_NAME => $this->computeIntervalColumnName($columnIndex),
                ColumnModel::COLUMN_TYPE => Columns::COLUMN_IS_INTERVAL,
                ColumnModel::COLUMN_DATA_TYPE => Columns::INTERVAL_COLUMN_DATA_TYPE,
                ColumnModel::COLUMN_INDEX => Columns::INTERVAL_COLUMN_INDEX,
            ]);

            $columns->push($columnModel);
        }

        return $columns;
    }

    /**
     * Function used to get the generated column name based on given coordinates
     * @param int $index
     * @return string
     */
    protected function computeIntervalColumnName(int $index): string
    {
        $firstIntervalValue = $this->reportingTableModel->getValueForCoordinate($index - 1);
        $secondIntervalValue = $this->reportingTableModel->getValueForCoordinate($index);

        return sprintf(
            Columns::INTERVAL_COLUMN_NAME_TEMPLATE,
            $firstIntervalValue,
            $secondIntervalValue
        );
    }

    /**
     * Function used to create reporting table
     */
    protected function createTable()
    {
        list($success, $message) = $this->commonRepository->createTable($this->tableName, $this->tableStructure);

        if (!$success) {
            throw new CreateTableException(sprintf(
                CreateTableException::TABLE_FAILED_TO_CREATE,
                $this->tableName,
                $message ?? CreateTableException::UNKNOWN_REASON
            ));
        }
    }

    /**
     * Function used to compute job that handles the table creation
     * @return ReportingTable
     */
    protected function getReportingTableJob(): ReportingTable
    {
        /** @var ReportingTable $reportingTableModel */
        $reportingTableModel = ReportingTableFactory::build($this->tableInterval);

        $reportingTableModel->init($this->referenceDate, $this->dataInterval);

        return $reportingTableModel;
    }

}
