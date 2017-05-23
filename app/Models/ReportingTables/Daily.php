<?php
/**
 * Created by PhpStorm.
 * User: chase
 * Date: 24/04/17
 * Time: 16:34
 */

namespace App\Models\ReportingTables;


use Carbon\Carbon;

class Daily extends ReportingTable
{


    /**
     * Function used to compute table name based on given table interval and reference date
     * @return string
     */
    public function getTableName(): string
    {
        $referenceDate = $this->referenceDate->format('Y_m_d');
        $className = (new \ReflectionClass($this))->getShortName();

        $tableName = $className . "_" . $referenceDate;

        return $tableName;
    }

    /**
     * Function used to return column count
     * @return int
     */
    public function getIntervalColumnCount(): int
    {
        /* Number of minutes in a day divided by number of minutes per interval */
        return 24 * 60 / $this->dataInterval;
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

        return $baseDate->format('H');
    }

    /**
     * Function used to retrieve the first datetime at which the table should hold information
     * @param Carbon $referenceDate
     * @return Carbon
     */
    public function getBaseDate(Carbon $referenceDate = null): Carbon
    {
        $referenceDate = $referenceDate ?? $this->referenceDate;

        return new Carbon($referenceDate->format('Y-m-d'));
    }

}