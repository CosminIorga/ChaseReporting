<?php
/**
 * Created by PhpStorm.
 * User: chase
 * Date: 23/06/17
 * Time: 15:45
 */

namespace App\Definitions;


use Monolog\Logger as MonologLogger;

class Logger
{
    const LOGGER_INSTANCE = '_instance';
    const MIN_LOG_LEVEL = 'min_level';
    const MEDIUMS = 'mediums';

    /**
     * Minimum default log level
     */
    const UNKNOWN_LOG_LEVEL = 'info';

    /**
     * Severity levels
     */
    const LEVELS = [
        'debug' => MonologLogger::DEBUG,
        'info' => MonologLogger::INFO,
        'notice' => MonologLogger::NOTICE,
        'warning' => MonologLogger::WARNING,
        'error' => MonologLogger::ERROR,
        'critical' => MonologLogger::CRITICAL,
        'alert' => MonologLogger::ALERT,
        'emergency' => MonologLogger::EMERGENCY,
    ];

    /**
     * Channel mediums
     */
    const REGISTER_TO_FILE_SYSTEM = 'registerToFileSystem';
}