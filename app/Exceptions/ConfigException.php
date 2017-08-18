<?php
/**
 * Created by PhpStorm.
 * User: chase
 * Date: 11/05/17
 * Time: 16:11
 */

namespace App\Exceptions;


class ConfigException extends DefaultException
{

    const TABLE_INTERVAL_NOT_ALLOWED = 'Table interval not allowed. Given: %s. Allowed %s';
    const DATA_INTERVAL_NOT_ALLOWED = 'Data interval not allowed. Given: %s. Allowed %s';

    const COLUMN_DATA_INCOMPLETE = 'Column data is incomplete. Missing key: %s';
    const INVALID_CONFIG_DATA_TYPE = 'Invalid config data type. Given: %s';
    const INVALID_CONFIG_INDEX = 'Invalid config index. Given: %s';
    const AGGREGATE_DATA_INCOMPLETE = 'Aggregate data is incomplete. Missing key: %s';
    const UNKNOWN_AGGREGATE_JSON_NAME = 'Unknown aggregate json name. Given: %s';
    const UNKNOWN_PIVOT_COLUMN_NAME = 'Unknown column pivot name. Given %s';

    const INCOMPLETE_FETCH_DATA = 'Incomplete fetch data. Missing: %s';
    const INCOMPLETE_FETCH_RECORD = 'Incomplete fetch record. Missing: %s';

    const INVALID_CONFIG_FUNCTION_RECEIVED = 'Invalid config function. Given: %s';
    const INVALID_CONFIG_EXTRA_KEY_RECEIVED = 'Invalid config extra key. Given: %s';

    const LOGGER_CHANNEL_MEDIUM_KEY_NOT_ARRAY = 'Logger channel medium must be of type array.';
    const LOGGER_CHANNEL_MEDIUM_NOT_EMPTY = 'Logger channel medium most not be empty';
    const LOGGER_INVALID_MINIMUM_LOG_LEVEL = 'Invalid minimum log level received. Given: %s';


    public function report()
    {
        \ChannelLog::error('default_channel', $this->getMessage(), $this->getContext());
    }
}