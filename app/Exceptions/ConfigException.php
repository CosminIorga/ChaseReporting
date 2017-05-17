<?php
/**
 * Created by PhpStorm.
 * User: chase
 * Date: 11/05/17
 * Time: 16:11
 */

namespace App\Exceptions;


use App\Interfaces\DefaultException;

class ConfigException extends \Exception implements DefaultException
{

    const TABLE_INTERVAL_NOT_ALLOWED = 'Table interval not allowed. Given: %s. Allowed %s';
    const DATA_INTERVAL_NOT_ALLOWED = 'Data interval not allowed. Given: %s. Allowed %s';
    const COLUMN_DATA_INCOMPLETE = 'Column data is incomplete. Missing key: %s';
    const AGGREGATE_DATA_INCOMPLETE = 'Aggregate data is incomplete. Missing key: %s';
    const UNKNOWN_AGGREGATE_JSON_NAME = 'Unknown aggregate json name. Given: %s';

    public function report()
    {
        // TODO: Implement report() method.
    }
}