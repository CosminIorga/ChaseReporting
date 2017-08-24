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
     * @param array $columnInformation
     * @param array $intervals
     * @param bool $flagParseQueryExtra
     * @return array
     */
    protected function mapIntervalsToQueryFunctions(
        array $columnInformation,
        array $intervals,
        bool $flagParseQueryExtra = false
    ): array {
        $columnName = $columnInformation[Data::COLUMN_NAME];
        $functionName = $columnInformation[Data::FUNCTION_NAME];
        $functionExtra = $columnInformation[Data::FUNCTION_PARAMS];

        $functionInformation = Functions::FUNCTIONS_MAPPING[$functionName];

        $columns = $this->extractColumn($columnName, $intervals, $functionInformation);

        /* Stringify columns */
        $columnsStringed = implode(
            $functionInformation[Functions::STRINGIFY_OPERATOR],
            $columns
        );

        /* Apply, if defined, the multiple_columns_aggregator if there are more than one columns */
        if (
            count($columns) > 1 &&
            array_key_exists(Functions::MULTIPLE_COLUMNS_AGGREGATOR, $functionInformation)
        ) {
            $columnsStringed = sprintf(
                $functionInformation[Functions::MULTIPLE_COLUMNS_AGGREGATOR],
                $columnsStringed
            );
        }

        /* Add group operator */
        $columnsStringed = sprintf(
            $functionInformation[Functions::GROUP_AGGREGATOR],
            $columnsStringed
        );

        /* Parse extra for column */
        if ($flagParseQueryExtra) {
            $columnsStringed = $this->parseQueryExtra($columnsStringed, $functionExtra);
        }

        /* Return column alias and computed query function */

        return [
            $columnInformation[Data::COLUMN_ALIAS],
            $columnsStringed,
        ];
    }

    /**
     * Small function used to compute the SQL extraction operation from a JSON
     * @param string $columnName
     * @param array $intervals
     * @param array $functionInformation
     * @return array
     */
    protected function extractColumn(string $columnName, array $intervals, array $functionInformation)
    {
        return array_map(function (string $interval) use (
            $columnName,
            $functionInformation
        ) {
            return sprintf(
                $functionInformation[Functions::ESCAPE_OPERATOR],
                sprintf(
                    Functions::EXTRACT_OPERATOR,
                    $interval,
                    $columnName
                )
            );
        }, $intervals);
    }

    /**
     * Function used to
     * @param string $column
     * @param array $functionExtra
     * @return string
     * @throws ConfigException
     */
    protected function parseQueryExtra(string $column, array $functionExtra): string
    {
        $extraConfig = $functionExtra ?? [];

        foreach ($extraConfig as $configKey => $configValue) {
            switch ($configKey) {
                case Data::AGGREGATE_EXTRA_ROUND:
                    return sprintf(
                        Functions::ROUND_FUNCTION,
                        $column,
                        $configValue
                    );
                default:
                    throw new ConfigException(
                        sprintf(
                            ConfigException::INVALID_CONFIG_EXTRA_KEY_RECEIVED,
                            $configKey
                        )
                    );
            }
        }

        return $column;
    }
}
