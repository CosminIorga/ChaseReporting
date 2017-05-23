<?php
/**
 * Created by PhpStorm.
 * User: chase
 * Date: 21/04/17
 * Time: 14:55
 */

return [

    /**
     * The timestamp key used to compute the table and record it needs to insert / update data
     */
    'timestamp_key' => [
        'name' => 'start_date',
        'dataType' => 'datetime',
        'allowNull' => false
    ],


    /**
     * The primary key configuration
     * Each "column" should be represented as an array containing:
     *    name => A string representing the column name
     *    dataType => The column type. See available column types in App\Definitions\Column
     *    dataTypeLength => The length for data type such as varchar(20) or int(11). Default: null
     *    index => See available indexes in App\Definitions\Columns. Default: "index"
     */
    'primary_key' => [
        'name' => 'hash_id',
        'dataType' => 'string',
        'dataTypeLength' => 255,
        'index' => 'primary',
    ],


    /**
     * The data that will create the pivot columns (Those used in group by clause)
     * All pivot columns will be used in a unique index as to better enforce the reporting algorithm
     */
    'pivots' => [
        [
            'name' => 'client',
            'dataType' => 'string',
            'dataTypeLength' => 255,
            'index' => 'index',
            'allowNull' => false,
        ],
        [
            'name' => 'carrier',
            'dataType' => 'string',
            'dataTypeLength' => 255,
            'index' => 'index',
            'allowNull' => false,
        ],
        [
            'name' => 'destination',
            'dataType' => 'string',
            'dataTypeLength' => 255,
            'index' => 'index',
            'allowNull' => false,
        ],
    ],


    /**
     * The interval column configuration
     */
    'intervals' => [
        'name' => 'interval_%1$s_%2$s',
        'dataType' => 'json',
        'index' => null,
        'allowNull' => true
    ],


    /**
     * Aggregate columns represent the columns on which various functions are applied
     * These are the ones that are stored in JSON format inside the main reporting table
     * Each aggregate column should be represented as an array containing:
     *      name => A string which represents the array key from where to fetch the data
     *      jsonName => The name under which the aggregated data is stored in the JSON column. Must be unique
     *      function => The function applied on the data found in 'name'. See available functions in App\Definitions\Functions
     *      extra => An array with various information such as:
     *          round => Before inserting the data in reports, round the value to X decimals. Only works for numeric values
     */
    'aggregates' => [
        [
            'name' => 'duration',
            'jsonName' => 'total_duration',
            'function' => 'sum',
            'extra' => [
                'round' => 2
            ]
        ],
        [
            'name' => 'cost',
            'jsonName' => 'total_cost',
            'function' => 'sum',
            'extra' => [
                'round' => 2
            ]
        ],
        [
            'name' => null,
            'jsonName' => 'total_records',
            'function' => 'count',
            'extra' => []
        ],
        [
            'name' => 'status',
            'jsonName' => 'distinct_records',
            'function' => 'distinct',
            'extra' => [
                'counter' => true
            ]
        ],
    ],

];