<?php
/**
 * Created by PhpStorm.
 * User: chase
 * Date: 26/04/17
 * Time: 18:10
 */

namespace App\Exceptions;


use App\Definitions\Logger;

class ModifyDataException extends DefaultException
{

    const DATA_IS_EMPTY = 'Received data is an empty array';
    const INCOMPLETE_RECORD = 'Record does not contain all necessary data. Missing: %s';
    const INVALID_MODIFY_DATA_OPERATION = 'Invalid modify data operation. Given: %s';
    const RECORD_NOT_FOUND = "Record does not exist";

    public function report()
    {
        \ChannelLog::error(Logger::MODIFY_DATA_CHANNEL, $this->getMessage(), $this->getContext());
    }
}