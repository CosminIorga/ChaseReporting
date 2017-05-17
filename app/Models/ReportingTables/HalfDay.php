<?php

namespace App\Models\ReportingTables;


use Carbon\Carbon;

class HalfDay extends ReportingTable
{

    /**
     * Function used to compute table name based on given table interval and reference date
     * @return string
     */
    public function getTableName(): string
    {
        $className = (new \ReflectionClass($this))->getShortName();

        $tableName = $className . "_" . $this->getBaseDate()->format('Y_m_d_H_i');

        return $tableName;
    }

    /**
     * Function used to return the column count for given table interval
     * @return int
     */
    public function getIntervalColumnCount(): int
    {
        /* Number of minutes in half of a day divided by number of minutes per interval */
        return 12 * 60 / $this->dataInterval;
    }

    /**
     * Function used to return a value for given coordinate based on table interval
     * @param int $coordinate
     * @return string
     */
    public function getValueForCoordinate(int $coordinate): string
    {
        $baseDate = $this->getBaseDate();

        $baseDate->addMinutes($this->dataInterval * $coordinate);

        return $baseDate->format('H_i');
    }

    /**
     * Function used to retrieve the first datetime at which the table should hold information
     * @return Carbon
     */
    public function getBaseDate(): Carbon
    {
        $referenceDate = clone $this->referenceDate;

        $fullHalves = intval($this->referenceDate->format('H') / 12);

        $referenceDate->setTime($fullHalves * 12, 0, 0);

        return $referenceDate;
    }
}