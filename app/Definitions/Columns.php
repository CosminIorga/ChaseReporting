<?php
/**
 * Created by PhpStorm.
 * User: chase
 * Date: 21/04/17
 * Time: 17:16
 */

namespace App\Definitions;


class Columns
{

    /**
     * Available column types
     */
    const COLUMN_PRIMARY = 'primary';
    const COLUMN_PIVOT = 'pivot';
    const COLUMN_TIMESTAMP = 'timestamp';
    const COLUMN_INTERVAL = 'interval';

    const AVAILABLE_COLUMN_TYPES = [
        self::COLUMN_PRIMARY,
        self::COLUMN_PIVOT,
        self::COLUMN_TIMESTAMP,
        self::COLUMN_INTERVAL,
    ];

    /**
     * Available column data types
     */
    const COLUMN_DATA_TYPE_STRING = 'string';
    const COLUMN_DATA_TYPE_INT = 'integer';
    const COLUMN_DATA_TYPE_JSON = 'json';
    const COLUMN_DATA_TYPE_DATETIME = 'datetime';

    const AVAILABLE_COLUMN_DATA_TYPES = [
        self::COLUMN_DATA_TYPE_INT,
        self::COLUMN_DATA_TYPE_STRING,
        self::COLUMN_DATA_TYPE_JSON,
        self::COLUMN_DATA_TYPE_DATETIME,
    ];


    /**
     * Available column indexes
     */
    const COLUMN_PRIMARY_INDEX = 'primary';
    const COLUMN_UNIQUE_INDEX = 'unique';
    const COLUMN_SIMPLE_INDEX = 'index';
    const COLUMN_NO_INDEX = null;

    const AVAILABLE_COLUMN_INDEXES = [
        Columns::COLUMN_SIMPLE_INDEX,
        Columns::COLUMN_UNIQUE_INDEX,
        Columns::COLUMN_PRIMARY_INDEX,
        Columns::COLUMN_NO_INDEX,
    ];
}
