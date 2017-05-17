<?php
/**
 * Created by PhpStorm.
 * User: chase
 * Date: 15/05/17
 * Time: 17:30
 */

namespace App\Models\AggregateFunctions;


use App\Definitions\Data;
use App\Services\ConfigGetter;

abstract class DefaultFunction
{
    /**
     * @var ConfigGetter
     */
    protected $configGetter;

    /**
     * Variable used to hold the currently used aggregate configuration
     * @var array
     */
    protected $aggregateConfig;


    /**
     * Small function used to initialize various fields
     * @param array $aggregateConfig
     */
    public function init(array $aggregateConfig)
    {
        $this->configGetter = ConfigGetter::Instance();
        $this->aggregateConfig = $aggregateConfig;
    }

    /**
     * Function used to parse the "extra" key in aggregate config and modify value accordingly
     * @param string $value
     * @return string
     */
    protected function parseExtra(string $value): string
    {
        /* Return value if "extra" key is empty or does not exist */
        if (
            !array_key_exists(Data::AGGREGATE_EXTRA, $this->aggregateConfig) ||
            empty($this->aggregateConfig[Data::AGGREGATE_EXTRA])
        ) {
            return $value;
        }

        /* Iterate over */
        foreach ($this->aggregateConfig[Data::AGGREGATE_EXTRA] as $extraKey => $extraValue) {
            switch ($extraKey) {
                case Data::AGGREGATE_EXTRA_ROUND:
                    $value = round($value, $extraValue);
                    break;
                case Data::AGGREGATE_EXTRA_COUNTER:
                    //TODO: figure out if and how to implement this
                    break;
                default:
                    /* Do nothing with the value if action is not registered */
                    break;
            }
        }

        /* Always return as string to enforce a single type */
        return strval($value);
    }

    /**
     * Function used to aggregate two values. Aggregation method is decided by extending function
     * @param string $value1
     * @param string $value2
     * @return string
     */
    abstract public function aggregateTwoValues(string $value1, string $value2 = null): string;

    /**
     * Function used to compute aggregate value given a record
     * @param array $record
     * @return string
     */
    abstract public function getAggregateValue(array $record): string;
}