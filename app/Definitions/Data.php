<?php
/**
 * Created by PhpStorm.
 * User: chase
 * Date: 21/04/17
 * Time: 14:56
 */

namespace App\Definitions;


class Data
{
    /**
     * Value returned if to-be-inserted record does not contain the corresponding key for an aggregator
     */
    const EMPTY_VALUE = '';

    /**
     * Config column definitions (Primary Column, Pivot Columns, Interval Columns and Timestamp Column)
     */
    const CONFIG_COLUMN_NAME = 'name';
    const CONFIG_COLUMN_DATA_TYPE = 'dataType';
    const CONFIG_COLUMN_DATA_TYPE_LENGTH = 'dataTypeLength';
    const CONFIG_COLUMN_INDEX = 'index';
    const CONFIG_COLUMN_ALLOW_NULL = 'allowNull';
    const CONFIG_COLUMN_TYPE = 'type';

    /**
     * Aggregated column definitions
     */
    const AGGREGATE_NAME = 'name';
    const AGGREGATE_JSON_NAME = 'jsonName';
    const AGGREGATE_FUNCTION = 'function';
    const AGGREGATE_EXTRA = 'extra';

    const AGGREGATE_EXTRA_ROUND = 'round';
    const AGGREGATE_EXTRA_COUNTER = 'counter';

}