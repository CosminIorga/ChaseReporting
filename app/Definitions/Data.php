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
    const EMPTY_VALUE = null;
    const ONE_UNIT = 1;

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
     * Definitions for an insert record
     */
    const INSERT_RECORD_PRIMARY_KEY_VALUE = "primaryKeyValue";
    const INSERT_RECORD_TABLE_NAME = "tableName";
    const INSERT_RECORD_FIXED_DATA = "fixedData";
    const INSERT_RECORD_VOLATILE_DATA = "volatileData";

    /**
     * Aggregated column definitions
     */
    const AGGREGATE_JSON_NAME = 'json_name';

    const AGGREGATE_INPUT_NAME = 'input_name';
    const AGGREGATE_INPUT_FUNCTION = 'input_function';
    const AGGREGATE_OUTPUT_FUNCTIONS = 'output_functions';
    const AGGREGATE_EXTRA = 'extra';

    const AGGREGATE_EXTRA_ROUND = 'round';

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

    /**
     * Fetch query-specific information
     */
    const DATA_COLUMN_ALIAS = '%1$s_%2$s';
    const HASH_COLUMN_ALIAS = 'hashColumn';


}