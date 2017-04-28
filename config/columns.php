<?php
/**
 * Created by PhpStorm.
 * User: chase
 * Date: 21/04/17
 * Time: 14:55
 */

return [

    /**
     * The timestamp key used to compute the table it needs to insert the data in
     */
    'timestamp_key' => [
        'name' => 'start_date',
    ],


    /**
     * The data that will create the pivot columns (Those used in group by clause)
     * Each "column" should be represented as an array containing:
     *      name => A string representing the column name
     *      dataType => The column type. See available column types in App\Definitions\Columns
     *      extra => An array containing various information such as:
     *          index => See available indexes in App\Definitions\Columns. Default: "index"
     *          dataTypeLength => The length for data type such as varchar(20) or int(11). Default: null
     * All pivot columns will be used in a unique index as to better enforce the reporting algorithm
     */
    'pivots' => [
        [
            'name' => 'pivot1',
            'dataType' => 'integer',
            'index' => 'index',
            'extra' => [
                'dataTypeLength' => null,
            ]
        ],
        [
            'name' => 'pivot2',
            'dataType' => 'string',
            'index' => 'index',
            'extra' => [
                'dataTypeLength' => 20,
            ]
        ],
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
                'round' => 4
            ]
        ],
        [
            'name' => 'records',
            'jsonName' => 'total_records',
            'function' => 'count',
            'extra' => []
        ],
        [
            'name' => 'records',
            'jsonName' => 'distinct_records',
            'function' => 'distinct',
            'extra' => []
        ],
    ]

];