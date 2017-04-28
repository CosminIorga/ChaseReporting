<?php
/**
 * Created by PhpStorm.
 * User: chase
 * Date: 14/04/17
 * Time: 16:27
 */

namespace App\Exceptions;


use App\Interfaces\DefaultException;

class CreateTableException extends \Exception implements DefaultException
{


    /**
     * Exception messages
     */
    const INVALID_TABLE_INTERVAL = "Received invalid table interval: %s";
    const INVALID_DATA_INTERVAL = "Received invalid data interval: %s";
    const DATA_INTERVAL_NOT_ALLOWED = "Data interval not allowed for table type: %s";
    const INVALID_REFERENCE_DATE = "Reference date must be of type datetime";

    const TABLE_FAILED_TO_CREATE = "Table %1\$s failed to create. Reason: %2\$s ";
    const TABLE_WAS_CREATED_WITH_WARNINGS = "Table was created with following warnings: %s";
    const TABLE_ALREADY_EXISTS = "Table %s already exists";
    const UNKNOWN_REASON = "Unknown reason";

    public function report()
    {
        // TODO: Implement report() method.
    }
}