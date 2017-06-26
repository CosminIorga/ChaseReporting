<?php
/**
 * Created by PhpStorm.
 * User: chase
 * Date: 11/05/17
 * Time: 13:01
 */

namespace App\Exceptions;



class ServiceException extends DefaultException
{

    const SERVICE_GETTER_INVALID_FUNCTION = 'Computer not defined for variable %s';

    public function report()
    {
        \ChannelLog::error('default_channel', $this->getMessage(), $this->getContext());
    }
}
