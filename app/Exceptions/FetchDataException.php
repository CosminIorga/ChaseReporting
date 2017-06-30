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

    public function report()
    {
        \ChannelLog::error(Logger::FETCH_DATA_CHANNEL, $this->getMessage(), $this->getContext());
    }
}