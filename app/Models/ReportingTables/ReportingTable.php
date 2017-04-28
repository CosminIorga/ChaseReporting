<?php

namespace App\Models\ReportingTables;

use DateTime;

abstract class ReportingTable
{

    /**
     * The reference date
     * @var DateTime
     */
    protected $referenceDate;

    /**
     * Data interval
     * @var string
     */
    protected $dataInterval;


    /**
     * Function used to initialize various fields
     * @param DateTime $referenceDate
     * @param string $dataInterval
     */
    public function init(DateTime $referenceDate, string $dataInterval)
    {
        $this->referenceDate = $referenceDate;
        $this->dataInterval = $dataInterval;
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
}
