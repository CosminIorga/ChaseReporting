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
    const COLUMN_IS_PRIMARY = 'is_primary';
    const COLUMN_IS_PIVOT = 'is_pivot';
    const COLUMN_IS_INTERVAL = 'is_interval';

    const AVAILABLE_COLUMN_TYPES = [
        self::COLUMN_IS_PRIMARY,
        self::COLUMN_IS_PIVOT,
        self::COLUMN_IS_INTERVAL,
    ];


    /**
     * Available column data types
     */
    const COLUMN_DATA_TYPE_STRING = 'string';
    const COLUMN_DATA_TYPE_INT = 'integer';
    const COLUMN_DATA_TYPE_JSON = 'json';

    const AVAILABLE_COLUMN_DATA_TYPES = [
        self::COLUMN_DATA_TYPE_INT,
        self::COLUMN_DATA_TYPE_STRING,
        self::COLUMN_DATA_TYPE_JSON,
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


    /**
     * Primary column definitions
     */
    const PRIMARY_COLUMN_NAME = 'id';
    const PRIMARY_COLUMN_DATA_TYPE = self::COLUMN_DATA_TYPE_INT;
    const PRIMARY_COLUMN_INDEX = self::COLUMN_PRIMARY_INDEX;

    /**
     * Interval-Generated column definitions
     */
    const INTERVAL_COLUMN_NAME_TEMPLATE = 'interval_%1$s_%2$s';
    const INTERVAL_COLUMN_DATA_TYPE = self::COLUMN_DATA_TYPE_JSON;
    const INTERVAL_COLUMN_INDEX = self::COLUMN_NO_INDEX;
}
