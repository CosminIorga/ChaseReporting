<?php
/**
 * Created by PhpStorm.
 * User: chase
 * Date: 11/05/17
 * Time: 13:01
 */

namespace App\Exceptions;


use App\Interfaces\DefaultException;

class ServiceException extends \Exception implements DefaultException
{

    const SERVICE_GETTER_INVALID_FUNCTION = 'Computer not defined for variable %s';

    public function report()
    {
        // TODO: Implement report() method.
    }
}
