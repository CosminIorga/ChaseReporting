<?php
/**
 * Created by PhpStorm.
 * User: chase
 * Date: 30/05/17
 * Time: 13:07
 */

namespace App\Traits;


use App\Definitions\Data;
use App\Definitions\Functions as FunctionsDefinitions;
use App\Exceptions\ConfigException;

trait Functions
{

    /**
     * Function used to return aggregate value given record and aggregate config
     * @param array $record
     * @param array $aggregateConfig
     * @return string
     * @throws ConfigException
     */
    protected function getAggregateValue(array $record, array $aggregateConfig): string
    {
        switch ($aggregateConfig[Data::AGGREGATE_FUNCTION]) {
            case FunctionsDefinitions::FUNCTION_SUM:
            case FunctionsDefinitions::FUNCTION_DISTINCT:
                $columnName = $aggregateConfig[Data::AGGREGATE_NAME];

                /* Return empty string if value does not exist in given record */
                if (!array_key_exists($columnName, $record)) {
                    return Data::EMPTY_VALUE;
                }

                /* Otherwise return the value from the record */
                return strval($record[$columnName]);

                break;
            case FunctionsDefinitions::FUNCTION_COUNT:
                /* Always return one unit. Duh ... we count here */
                return strval(1);
            default:
                throw new ConfigException(
                    sprintf(
                        ConfigException::INVALID_CONFIG_FUNCTION_RECEIVED,
                        $aggregateConfig[Data::AGGREGATE_FUNCTION]
                    )
                );
        }
    }

    /**
     * Function used to aggregate an array of values based on given aggregate config
     * @param array $values
     * @param array $aggregateConfig
     * @return mixed
     * @throws ConfigException
     */
    protected function aggregateValues(array $values, array $aggregateConfig)
    {
        switch ($aggregateConfig[Data::AGGREGATE_FUNCTION]) {
            case FunctionsDefinitions::FUNCTION_SUM:
            case FunctionsDefinitions::FUNCTION_COUNT:
                return array_sum($values);
                break;
            case FunctionsDefinitions::FUNCTION_DISTINCT:
                $data = array_unique(explode(', ', implode(', ', $values)));
                sort($data);
                return implode(', ', $data);
            default:
                throw new ConfigException(
                    sprintf(
                        ConfigException::INVALID_CONFIG_FUNCTION_RECEIVED,
                        $aggregateConfig[Data::AGGREGATE_FUNCTION]
                    )
                );
        }
    }

}