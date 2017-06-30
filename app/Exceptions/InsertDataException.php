<?php
/**
 * Created by PhpStorm.
 * User: chase
 * Date: 26/04/17
 * Time: 18:10
 */

namespace App\Exceptions;


use App\Definitions\Logger;

class InsertDataException extends DefaultException
{

    const DATA_IS_EMPTY = 'Received data is an empty array';
    const INCOMPLETE_RECORD = 'Record does not contain all necessary data. Missing: %s';

    public function report()
    {
        \ChannelLog::error(Logger::INSERT_DATA_CHANNEL, $this->getMessage(), $this->getContext());
    }
}