<?php
/**
 * Created by PhpStorm.
 * User: chase
 * Date: 30/06/17
 * Time: 13:25
 */

namespace App\Traits;


use App\Definitions\Data;
use App\Definitions\Functions;
use App\Exceptions\ConfigException;

trait InputFunctions
{
    /**
     * Function used to return aggregate value given record and aggregate config
     * @param string $operation
     * @param array $record
     * @param array $aggregateConfig
     * @return int|null|string
     * @throws ConfigException
     */
    protected function getAggregateValue(string $operation, array $record, array $aggregateConfig)
    {
        $columnName = $aggregateConfig[Data::AGGREGATE_INPUT_NAME];
        $sign = $this->returnSignByOperation($operation);

        switch ($aggregateConfig[Data::AGGREGATE_INPUT_FUNCTION]) {
            case Functions::FUNCTION_SUM:
                /* Return empty string if value does not exist in given record */
                if (!array_key_exists($columnName, $record)) {
                    return Data::EMPTY_VALUE;
                }

                /* Otherwise return the value from the record */

                return $sign * $record[$columnName];
            case Functions::FUNCTION_COUNT:
                /* Always return one unit if input_name is null */
                if (is_null($columnName)) {
                    return $sign * Data::ONE_UNIT;
                }

                /* Otherwise evaluate column and check if value is considered non-zero */

                return $sign * intval(boolval($record[$columnName]));
            default:
                throw new ConfigException(
                    sprintf(
                        ConfigException::INVALID_CONFIG_FUNCTION_RECEIVED,
                        $aggregateConfig[Data::AGGREGATE_INPUT_FUNCTION]
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
        switch ($aggregateConfig[Data::AGGREGATE_INPUT_FUNCTION]) {
            case Functions::FUNCTION_SUM:
            case Functions::FUNCTION_COUNT:
                $result = array_sum($values);
                break;
            case Functions::FUNCTION_MAX:
                $result = max($values);
                break;
            case Functions::FUNCTION_MIN:
                $result = min($values);
                break;
            default:
                throw new ConfigException(
                    sprintf(
                        ConfigException::INVALID_CONFIG_FUNCTION_RECEIVED,
                        $aggregateConfig[Data::AGGREGATE_INPUT_FUNCTION]
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

    /**
     * Short function used to return 1 or -1 depending on the given operation
     * @param string $operation
     * @return int
     */
    protected function returnSignByOperation(string $operation): int
    {
        return ($operation == Data::MODIFY_DATA_OPERATION_INSERT) ? 1 : -1;
    }
}