<?php
/**
 * Created by PhpStorm.
 * User: chase
 * Date: 17/05/17
 * Time: 17:57
 */

namespace App\Exceptions;



use App\Definitions\Logger;

class FetchDataException extends DefaultException
{

    const DATA_IS_EMPTY = "Data is empty";
    const MISSING_KEY = "Missing key: %s";
    const INVALID_COLUMN_VALUE = "Invalid column value. Given: %s";
    const INVALID_FUNCTION_VALUE = "Invalid function value. Given: %s";
    const INVALID_PIVOT_VALUE = "Invalid pivot value. Given: %s";

    const INVALID_INTERVAL_FORMAT = "Invalid interval format";
    const END_DATE_LOWER_THAN_START_DATE = "Start date must be lower than end date";
    const INVALID_ORDER_BY_COLUMN_DIRECTION = "Invalid order by column direction";
    const INVALID_ORDER_BY_COLUMN_NAME = "Invalid order by column name";
    const COLUMN_MUST_NOT_BE_NULL = "Column %s must not be null";
    const COLUMN_MUST_NOT_BE_EMPTY = "Column %s must not be empty";

    public function report()
    {
        \ChannelLog::error(Logger::FETCH_DATA_CHANNEL, $this->getMessage(), $this->getContext());
    }
}