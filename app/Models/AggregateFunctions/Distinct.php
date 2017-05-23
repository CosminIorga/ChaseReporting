<?php
/**
 * Created by PhpStorm.
 * User: chase
 * Date: 15/05/17
 * Time: 17:48
 */

namespace App\Models\AggregateFunctions;


use App\Definitions\Data;

class Distinct extends DefaultFunction
{

    /**
     * Function used to aggregate two values. Aggregation method is decided by extending function
     * @param string $value1
     * @param string $value2
     * @return mixed
     */
    public function aggregateTwoValues(string $value1, string $value2 = null): string
    {
        /* Explode values */
        $values1 = explode(', ', $value1);
        $values2 = explode(', ', $value2);

        /* Add new value to current values */
        $currentValues = array_merge($values1, $values2);

        /* Filter empty values */
        $currentValues = array_filter($currentValues);

        /* Remove duplicates */
        $currentValues = array_unique($currentValues);

        /* Sort alphabetically current values */
        sort($currentValues);

        return implode(', ', $currentValues);
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