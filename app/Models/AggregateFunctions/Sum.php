<?php
/**
 * Created by PhpStorm.
 * User: chase
 * Date: 15/05/17
 * Time: 17:38
 */

namespace App\Models\AggregateFunctions;


use App\Definitions\Data;

class Sum extends DefaultFunction
{

    /**
     * Function used to aggregate two values. Aggregation method is decided by extending function
     * @param string $value1
     * @param string $value2
     * @return string
     */
    public function aggregateTwoValues(string $value1, string $value2 = null): string
    {
        $sum = floatval($value1) + floatval($value2);

        $sum = floatval($this->parseExtra(strval($sum)));

        return strval($sum);
    }

    /**
     * Function used to compute aggregate value given a record
     * @param array $record
     * @return string
     */
    public function getAggregateValue(array $record): string
    {
        $columnName = $this->aggregateConfig[Data::AGGREGATE_NAME];

        /* Return empty string if value does not exist in given record */
        if (!array_key_exists($columnName, $record)) {
            return Data::EMPTY_VALUE;
        }

        /* Otherwise return the value from the record */
        return strval($record[$columnName]);
    }
}
