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

    /**
     * Fetch column definitions
     */
    const FETCH_INTERVAL_START = 'intervalStart';
    const FETCH_INTERVAL_END = 'intervalEnd';
    const FETCH_COLUMNS = 'columns';
    const FETCH_GROUP_CLAUSE = 'groupClause';
    const FETCH_WHERE_CLAUSE = 'whereClause';
    const FETCH_ORDER_CLAUSE = 'orderClause';

    /**
     * Fetch query data definitions
     */
    const FETCH_QUERY_DATA_TABLE = 'table';
    const FETCH_QUERY_DATA_COLUMNS = 'columns';
    const FETCH_QUERY_DATA_WHERE_CLAUSE = 'where';
    const FETCH_QUERY_DATA_GROUP_CLAUSE = 'group';
    const FETCH_QUERY_DATA_ORDER_CLAUSE = 'order';

    /**
     * Fetch query-specific information
     */
    const COLUMN_ALIAS = 'preMergedData';
    const CONCAT_SEPARATOR = ' ||| ';

}