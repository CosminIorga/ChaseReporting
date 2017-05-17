<?php
/**
 * Created by PhpStorm.
 * User: chase
 * Date: 15/05/17
 * Time: 17:47
 */

namespace App\Models\AggregateFunctions;


class Count extends DefaultFunction
{

    /**
     * Function used to aggregate two values. Aggregation method is decided by extending function
     * @param string $value1
     * @param string $value2
     * @return mixed
     */
    public function aggregateTwoValues(string $value1, string $value2 = null): string
    {
        /* Check if value is numeric */
        if (is_numeric($value2)) {
            return strval(intval($value2) + intval($value1));
        }

        /* Otherwise assume empty string or any other type of value as zero */
        return $value1;
    }

    /**
     * Function used to compute aggregate value given a record
     * @param array $record
     * @return string
     */
    public function getAggregateValue(array $record): string
    {
        /* Always return one unit. Duh ... we count here */
        return strval(1);
    }
}