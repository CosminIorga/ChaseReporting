<?php

namespace App\Models\ReportingTables;


use App\Definitions\Data;
use App\Services\ConfigGetter;
use Carbon\Carbon;

abstract class ReportingTable
{

    /**
     * The reference date
     * @var Carbon
     */
    protected $referenceDate;

    /**
     * Data interval
     * @var string
     */
    protected $dataInterval;

    /**
     * The config getter
     * @var ConfigGetter
     */
    protected $configGetter;


    /**
     * Function used to initialize various fields
     * @param Carbon $referenceDate
     * @param string $dataInterval
     */
    public function init(Carbon $referenceDate, string $dataInterval)
    {
        $this->referenceDate = $referenceDate;
        $this->dataInterval = $dataInterval;

        $this->configGetter = ConfigGetter::Instance();
    }

    /**
     * Short function used to dynamically change the reference date
     * @param Carbon $referenceDate
     */
    public function setReferenceDate(Carbon $referenceDate)
    {
        $this->referenceDate = $referenceDate;
    }


    /**
     * Function used to compute interval column name based on given index
     * @param int $index
     * @return string
     */
    public function getIntervalColumnByIndex(int $index): string
    {
        $intervalColumnData = $this->configGetter->intervalColumnData;

        $intervalColumnNameTemplate = $intervalColumnData[Data::CONFIG_COLUMN_NAME];

        return sprintf(
            $intervalColumnNameTemplate,
            $this->getValueForCoordinate($index - 1),
            $this->getValueForCoordinate($index)
        );
    }

    /**
     * Function used to retrieve the interval column given a reference date
     * @param Carbon $referenceDate
     * @return string
     */
    public function getIntervalColumnByReferenceData(Carbon $referenceDate = null): string
    {
        if (is_null($referenceDate)) {
            $referenceDate = $this->referenceDate;
        }

        $baseDate = is_null($referenceDate) ? $this->getBaseDate() : $this->getBaseDate($referenceDate);

        $difference = $referenceDate->getTimestamp() - $baseDate->getTimestamp();

        $index = intval(($difference / ($this->dataInterval * 60)) + 1);

        return $this->getIntervalColumnByIndex($index);
    }

    /**
     * Function used to compute table name based on given table interval and reference date
     * @return string
     */
    abstract public function getTableName(): string;

    /**
     * Function used to return the column count for given table interval
     * @return int
     */
    abstract public function getIntervalColumnCount(): int;

    /**
     * Function used to return a value for given coordinate based on table interval
     * @param int $coordinate
     * @return string
     */
    abstract public function getValueForCoordinate(int $coordinate): string;

    /**
     * Function used to retrieve the first datetime at which the table should hold information
     * @param Carbon $referenceDate
     * @return Carbon
     */
    abstract public function getBaseDate(Carbon $referenceDate = null): Carbon;

}
