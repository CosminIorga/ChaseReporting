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
     * @return string|int|null
     * @throws ConfigException
     */
    protected function getAggregateValue(array $record, array $aggregateConfig)
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
                return $record[$columnName];

                break;
            case FunctionsDefinitions::FUNCTION_COUNT:
                /* Always return one unit. Duh ... we count here */
                return 1;
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
                $result = array_sum($values);
                break;
            case FunctionsDefinitions::FUNCTION_DISTINCT:
                $data = array_unique(explode(', ', implode(', ', $values)));
                sort($data);
                $result = implode(Data::DISTINCT_RECORDS_SEPARATOR, $data);
                break;
            default:
                throw new ConfigException(
                    sprintf(
                        ConfigException::INVALID_CONFIG_FUNCTION_RECEIVED,
                        $aggregateConfig[Data::AGGREGATE_FUNCTION]
                    )
                );
        }

        return $this->parseExtra($result, $aggregateConfig);
    }

    /**
     * Function used to parse the extra config
     * @param mixed $result
     * @param array $aggregateConfig
     * @return mixed
     * @throws ConfigException
     */
    protected function parseExtra($result, array $aggregateConfig)
    {
        $extraConfig = $aggregateConfig[Data::AGGREGATE_EXTRA] ?? [];

        foreach ($extraConfig as $configKey => $configValue) {
            switch ($configKey) {
                case Data::AGGREGATE_EXTRA_ROUND:
                    return round($result, $configValue);
                    break;
                case Data::AGGREGATE_EXTRA_COUNTER:
                    if (!$configValue) {
                        return $result;
                    }

                    $jsonName = $aggregateConfig[Data::AGGREGATE_JSON_NAME];

                    return [
                        $jsonName => $result,
                        $jsonName . "_" . Data::DISTINCT_RECORDS_COUNTER_FIELD => count(
                            explode(Data::DISTINCT_RECORDS_SEPARATOR, $result)
                        )
                    ];
                    break;
                default:
                    throw new ConfigException(
                        sprintf(
                            ConfigException::INVALID_CONFIG_EXTRA_KEY_RECEIVED,
                            $configKey
                        )
                    );
            }
        }

        return $result;
    }

}