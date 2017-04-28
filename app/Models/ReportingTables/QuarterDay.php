<?php

namespace App\Models\ReportingTables;


class QuarterDay extends ReportingTable
{

    /**
     * Function used to compute table name based on given table interval and reference date
     * @return string
     */
    public function getTableName(): string
    {
        $referenceDate = $this->referenceDate->format('Y_m_d');
        $className = (new \ReflectionClass($this))->getShortName();

        $tableName = $className . "_" . floor($this->referenceDate->format('H') / 6) . "_" . $referenceDate;

        return $tableName;
    }

    /**
     * Function used to return the column count for given table interval
     * @return int
     */
    public function getIntervalColumnCount(): int
    {
        /* Number of minutes in a quarter of a day divided by number of minutes per interval */
        return 6 * 60 / $this->dataInterval;
    }

    /**
     * Function used to return a value for given coordinate based on table interval
     * @param int $coordinate
     * @return string
     */
    public function getValueForCoordinate(int $coordinate): string
    {
        return $coordinate * $this->dataInterval;
    }
}
