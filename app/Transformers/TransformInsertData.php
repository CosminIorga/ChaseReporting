<?php
/**
 * Created by PhpStorm.
 * User: chase
 * Date: 28/04/17
 * Time: 16:05
 */

namespace App\Transformers;


use App\Definitions\Columns;
use App\Definitions\Data;
use App\Models\ReportingTables\ReportingTable;
use App\Services\ConfigGetter;
use App\Traits\Functions;

class TransformInsertData
{
    use Functions;

    /**
     * The record to be parsed
     * @var array
     */
    protected $record;

    /**
     * @var ReportingTable
     */
    protected $reportingTableModel;

    /**
     * Array used to store the config column mapping
     * @var array
     */
    protected $columnMapping;


    /**
     * Transform input data to match an array used by InsertDataModel
     * @param array $record
     * @param ReportingTable $reportingTableModel
     * @return array
     */
    public function toReportingData(array $record, ReportingTable $reportingTableModel): array
    {
        $this->init($record, $reportingTableModel);

        /* Compute pivot columns */
        $pivotColumns = $this->computePivotColumns();

        /* Compute hash column */
        $hashColumn = $this->computeHashColumn($pivotColumns);

        /* Compute aggregate columns */
        $aggregateColumn = $this->computeAggregateColumn();


        return array_merge($hashColumn, $pivotColumns, $aggregateColumn);
    }

    /**
     * Small function used to initialize fields
     * @param array $record
     * @param ReportingTable $reportingTableModel
     */
    protected function init(array $record, ReportingTable $reportingTableModel)
    {
        $this->record = $record;
        $this->reportingTableModel = $reportingTableModel;

        $this->columnMapping = (ConfigGetter::Instance())->getColumnMapping();
    }

    /**
     * Function used to compute hash column based on given pivot columns
     * @param array $pivotColumns
     * @return array
     */
    protected function computeHashColumn(array $pivotColumns): array
    {
        /* Take primary (hash) column */
        $hashColumn = array_filter($this->columnMapping, function ($columnInfo) {
            return ($columnInfo[Data::CONFIG_COLUMN_TYPE] == Columns::COLUMN_PRIMARY);
        });

        $hashColumn = current($hashColumn);

        /* Order pivot columns alphabetically by key */
        ksort($pivotColumns);

        $hashValue = md5(implode('__', $pivotColumns));

        return [
            $hashColumn[Data::CONFIG_COLUMN_NAME] => $hashValue
        ];
    }

    /**
     * Function used to compute hash column values based on current record
     * @return array
     */
    protected function computePivotColumns(): array
    {
        /* Take pivot columns */
        $pivotColumns = array_filter($this->columnMapping, function ($columnInfo) {
            return ($columnInfo[Data::CONFIG_COLUMN_TYPE] == Columns::COLUMN_PIVOT);
        });

        $pivotColumnNames = array_column($pivotColumns, Data::CONFIG_COLUMN_NAME);

        /* Take pivot values from record */
        $pivotValues = array_intersect_key($this->record, array_flip($pivotColumnNames));

        return $pivotValues;
    }


    /**
     * Function used to compute aggregate column value based on current record and config aggregate data
     * @return array
     */
    protected function computeAggregateColumn(): array
    {
        /* Compute aggregate column name */
        $aggregateColumnName = $this->reportingTableModel->getIntervalColumnByReferenceData();

        /* Get aggregate config data */
        $aggregateConfigData = (ConfigGetter::Instance())->aggregateData;

        $aggregateData = [];

        /* Iterate over config data */
        array_walk($aggregateConfigData, function (array $aggregateConfig) use (&$aggregateData) {
            $aggregateData[$aggregateConfig[Data::AGGREGATE_JSON_NAME]] =
                $this->getAggregateValue($this->record, $aggregateConfig);
        });

        /* Encode values as JSON */
        $value = json_encode($aggregateData);

        return [
            $aggregateColumnName => $value
        ];
    }

}