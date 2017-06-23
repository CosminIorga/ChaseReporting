<?php
/**
 * Created by PhpStorm.
 * User: chase
 * Date: 23/06/17
 * Time: 12:54
 */

namespace App\Contracts\Facades;


use Illuminate\Support\Facades\Facade;

/** @noinspection PhpUndefinedClassInspection / Suppressed due to ide-helper interference */

/**
 * Class ChannelLog
 * @package App\Contracts\Facades
 * @method alert
 */
class ChannelLog extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor(): string
    {
        return 'channelLogger';
    }
}