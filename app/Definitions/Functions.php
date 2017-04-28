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


}