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
     * Modify data operations
     */
    const MODIFY_DATA_OPERATION_INSERT = 'insert';
    const MODIFY_DATA_OPERATION_DELETE = 'delete';

    const ALLOWED_MODIFY_DATA_OPERATIONS = [
        self::MODIFY_DATA_OPERATION_DELETE,
        self::MODIFY_DATA_OPERATION_INSERT,
    ];

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
    const INSERT_RECORD_PRIMARY_KEY_VALUE = "primary_key_value";
    const INSERT_RECORD_TABLE_NAME = "table_name";
    const INSERT_RECORD_FIXED_COLUMNS = "fixed_columns";
    const INSERT_RECORD_AGGREGATE_COLUMNS = "aggregate_columns";

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
    const INTERVAL_START = 'interval_start';
    const INTERVAL_END = 'interval_end';
    const COLUMNS = 'columns';
    const COLUMN_NAME = 'column_name';
    const COLUMN_ALIAS = 'column_alias';
    const FUNCTION_NAME = 'function_name';
    const FUNCTION_PARAMS = 'function_params';
    const GROUP_CLAUSE = 'group_clause';
    const WHERE_CLAUSE = 'where_clause';
    const ORDER_CLAUSE = 'order_clause';

    /**
     * Fetch query data definitions
     */
    const OPERATION_CREATE_TABLE = 'operation_create_table';
    const OPERATION_FETCH_REPORTING_DATA = 'operation_fetch_reporting_data';
    const OPERATION_FETCH_TEMPORARY_DATA = 'operation_fetch_temporary_data';
    const OPERATION_DROP_TABLE = 'operation_drop_table';

    const TEMPORARY_TABLE_NAME = 'temporary_table_name';
    const TEMPORARY_TABLE_NAME_TEMPLATE = 'aggregateDataTemp_';
    const TEMPORARY_TABLE_COLUMN_DEFINITIONS = 'temporary_table_column_definitions';
    const TEMPORARY_TABLE_AGGREGATE_COLUMN_NAME = 'aggregate_column';

    const FETCH_DATA = 'fetch_data';
    const FETCH_DATA_TABLE = 'fetch_data_table';
    const FETCH_DATA_COLUMNS = 'fetch_data_columns';
    const FETCH_DATA_WHERE_CLAUSE = 'fetch_data_where_clause';
    const FETCH_DATA_GROUP_CLAUSE = 'fetch_data_group_clause';
    const FETCH_DATA_MODE = 'fetch_data_mode';

    const FETCH_DATA_MODE_INSERT = 'fetch_data_mode_insert';
    const FETCH_DATA_MODE_SELECT = 'fetch_data_mode_select';

    /**
     * Parallel query information
     */
    const QUERY_DATA = 'query_data';
    const INSERTION_STATUS = 'insertion_status';
    const ERROR_MESSAGE = 'error_message';
}