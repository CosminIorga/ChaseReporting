<?php
/**
 * Created by PhpStorm.
 * User: chase
 * Date: 21/04/17
 * Time: 14:04
 */

namespace App\Definitions;


class Functions
{
    /**
     * Available aggregate functions
     */
    const FUNCTION_SUM = 'sum';
    const FUNCTION_COUNT = 'count';
    const FUNCTION_DISTINCT = 'distinct';

    const AVAILABLE_FUNCTIONS = [
        self::FUNCTION_SUM,
        self::FUNCTION_COUNT,
        self::FUNCTION_DISTINCT,
    ];

    /**
     * Associated MySQL aggregate operations for given functions
     */
    const AGGREGATE_FUNCTION_SUM = 'SUM(%1$s)';
    const AGGREGATE_FUNCTION_DISTINCT = 'GROUP_CONCAT(DISTINCT %1$s)';

    const FUNCTIONS_TO_AGGREGATES_MAPPING = [
        self::FUNCTION_SUM => self::AGGREGATE_FUNCTION_SUM,
        self::FUNCTION_COUNT => self::AGGREGATE_FUNCTION_SUM,
        self::FUNCTION_DISTINCT => self::AGGREGATE_FUNCTION_DISTINCT,
    ];

}