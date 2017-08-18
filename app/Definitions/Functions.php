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
    const FUNCTION_MAX = 'max';
    const FUNCTION_MIN = 'min';

    const ALLOWED_FUNCTIONS = [
        self::FUNCTION_SUM,
        self::FUNCTION_COUNT,
        self::FUNCTION_MAX,
        self::FUNCTION_MIN
    ];

    /**
     * Associated MySQL aggregate operations for given functions
     */
    const STRINGIFY_OPERATOR = 'stringify_operator';
    const GROUP_AGGREGATOR = 'group_aggregator';
    const ESCAPE_OPERATOR = 'escape_operator';
    const MULTIPLE_COLUMNS_AGGREGATOR = 'multiple_columns_aggregator';

    const HASH_FUNCTION = 'TO_BASE64(CONCAT(%1$s))';
    const EXTRACT_OPERATOR = 'JSON_EXTRACT(%1$s, "$.%2$s")';
    const IF_NULL_ESCAPE_FOR_NUMBERS = 'COALESCE(%1$s, 0)';
    const IF_NULL_ESCAPE_FOR_STRINGS = 'COALESCE(%1$s, "")';
    const JSON_OBJECT_FUNCTION = 'JSON_OBJECT(%1$s)';
    const ROUND_FUNCTION = 'ROUND(%1$s, %2$s)';

    const FUNCTIONS_MAPPING = [
        self::FUNCTION_SUM => [
            self::STRINGIFY_OPERATOR => ' + ',
            self::GROUP_AGGREGATOR => 'SUM(%1$s)',
            self::ESCAPE_OPERATOR => self::IF_NULL_ESCAPE_FOR_NUMBERS,
        ],
        self::FUNCTION_COUNT => [
            self::STRINGIFY_OPERATOR => ' + ',
            self::GROUP_AGGREGATOR => 'SUM(%1$s)',
            self::ESCAPE_OPERATOR => self::IF_NULL_ESCAPE_FOR_NUMBERS,
        ],
        self::FUNCTION_MAX => [
            self::STRINGIFY_OPERATOR => ', ',
            self::GROUP_AGGREGATOR => 'MAX(%1$s)',
            self::MULTIPLE_COLUMNS_AGGREGATOR => 'GREATEST(%1$s)',
            self::ESCAPE_OPERATOR => self::IF_NULL_ESCAPE_FOR_NUMBERS,
        ],
        self::FUNCTION_MIN => [
            self::STRINGIFY_OPERATOR => ', ',
            self::GROUP_AGGREGATOR => 'MIN(%1$s)',
            self::MULTIPLE_COLUMNS_AGGREGATOR => 'LEAST(%1$s)',
            self::ESCAPE_OPERATOR => self::IF_NULL_ESCAPE_FOR_NUMBERS,
        ]
    ];

}