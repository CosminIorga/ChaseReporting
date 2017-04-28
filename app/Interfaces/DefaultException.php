<?php
/**
 * Created by PhpStorm.
 * User: chase
 * Date: 01/02/17
 * Time: 17:56
 */

namespace App\Interfaces;

/**
 * Interface DefaultException
 * @package App\Exceptions
 * This interface should be extended by all custom exceptions
 */
interface DefaultException
{
    public function report();
}