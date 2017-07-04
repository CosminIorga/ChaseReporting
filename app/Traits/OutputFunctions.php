<?php
/**
 * Created by PhpStorm.
 * User: chase
 * Date: 30/05/17
 * Time: 13:07
 */

namespace App\Traits;


use App\Definitions\Data;
use App\Definitions\Functions;
use App\Exceptions\ConfigException;

trait OutputFunctions
{

    /**
     * Function used to map intervals to an SQL function
     * @param array $intervals
     * @param string $function
     * @param array $functionExtra
     * @param array $aggregateConfig
     * @return string
     */
    protected function mapIntervalsToQueryFunctions(
        array $intervals,
        string $function,
        array $functionExtra,
        array $aggregateConfig
    ): string {
        $aggregatesMapping = Functions::FUNCTIONS_MAPPING[$function];

        $columns = $this->extractColumn($aggregateConfig, $intervals, $aggregatesMapping);

        $columns = $this->parseQueryExtra($columns, $aggregateConfig, $functionExtra);

        /* Stringify columns */
        $columnsStringed = implode(
            $aggregatesMapping[Functions::STRINGIFY_OPERATOR],
            $columns
        );

        return sprintf(
            $aggregatesMapping[Functions::GROUP_AGGREGATOR],
            $columnsStringed,
            $this->computeColumnAlias($aggregateConfig, $function)
        );
    }

    /**
     * Small function used to compute the SQL extraction operation from a JSON
     * @param array $aggregateConfig
     * @param array $intervals
     * @param array $aggregatesMapping
     * @return array
     */
    protected function extractColumn(array $aggregateConfig, array $intervals, array $aggregatesMapping)
    {
        $jsonKey = $aggregateConfig[Data::AGGREGATE_JSON_NAME];

        $columns = array_map(function (string $interval) use ($jsonKey, $aggregatesMapping) {
            return sprintf(
                $aggregatesMapping[Functions::ESCAPE_OPERATOR],
                sprintf(
                    Functions::EXTRACT_OPERATOR,
                    $interval,
                    $jsonKey
                )
            );
        }, $intervals);

        return $columns;
    }

    /**
     * Function used to
     * @param array $columns
     * @param array $aggregateConfig
     * @param array $functionExtra
     * @return array
     * @throws ConfigException
     */
    protected function parseQueryExtra(array $columns, array $aggregateConfig, array $functionExtra): array
    {
        $extraConfig = $functionExtra ?? $aggregateConfig[Data::AGGREGATE_EXTRA] ?? [];

        foreach ($extraConfig as $configKey => $configValue) {
            switch ($configKey) {
                case Data::AGGREGATE_EXTRA_ROUND:
                    $configValue = ($configValue > 0) ? $configValue : 0;

                    $columns[] = number_format(0, $configValue, ".", "");

                    return $columns;
                default:
                    throw new ConfigException(
                        sprintf(
                            ConfigException::INVALID_CONFIG_EXTRA_KEY_RECEIVED,
                            $configKey
                        )
                    );
            }
        }

        return $columns;
    }

    /**
     * Short function used to return the column alias
     * @param array $aggregateConfig
     * @param string $function
     * @return string
     */
    protected function computeColumnAlias(array $aggregateConfig, string $function)
    {
        return sprintf(
            Data::DATA_COLUMN_ALIAS,
            $function,
            $aggregateConfig[Data::AGGREGATE_JSON_NAME]
        );
    }

}