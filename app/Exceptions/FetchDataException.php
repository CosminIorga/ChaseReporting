<?php
/**
 * Created by PhpStorm.
 * User: chase
 * Date: 17/05/17
 * Time: 17:57
 */

namespace Exceptions;


use App\Interfaces\DefaultException;
use Exception;

class FetchDataException extends Exception implements DefaultException
{

    const DATA_IS_EMPTY = "Data is empty";


    public function report()
    {
        // TODO: Implement report() method.
    }
}