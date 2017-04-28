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
     * Pivot column definitions
     */
    const PIVOT_NAME = 'name';
    const PIVOT_DATA_TYPE = 'dataType';
    const PIVOT_EXTRA = 'extra';

    const PIVOT_EXTRA_INDEX = 'index';
    const PIVOT_EXTRA_DATA_TYPE_LENGTH = 'dataTypeLength';


    /**
     * Aggregated column definitions
     */
    const AGGREGATE_NAME = 'name';
    const AGGREGATE_JSON_NAME = 'jsonName';
    const AGGREGATE_FUNCTION = 'function';
    const AGGREGATE_EXTRA = 'extra';

    const AGGREGATE_EXTRA_ROUND = 'round';

}