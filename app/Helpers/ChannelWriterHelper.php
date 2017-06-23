<?php
/**
 * Created by PhpStorm.
 * User: chase
 * Date: 23/06/17
 * Time: 15:48
 */

namespace App\Helpers;


use App\Definitions\Logger as LoggerHelper;
use Monolog\Formatter\LineFormatter;
use Monolog\Handler\RotatingFileHandler;
use Monolog\Logger;

class ChannelWriterHelper
{

    /**
     * Function used to register various handlers to the logger based on channel configuration
     * @param array $channel
     * @param string $channelName
     */
    protected function parseRegistering(array &$channel, string $channelName)
    {
        $channel[LoggerHelper::LOGGER_INSTANCE] = new Logger($channelName);

        /* Process each of the channel's broadcast mediums */
        $mediums = $channel[LoggerHelper::MEDIUMS];

        if (
            array_key_exists(LoggerHelper::REGISTER_TO_FILE_SYSTEM, $mediums)
            && $mediums[LoggerHelper::REGISTER_TO_FILE_SYSTEM]
        ) {
            $this->registerLoggerToFileSystem(
                $channel[LoggerHelper::LOGGER_INSTANCE],
                $channelName,
                $channel[LoggerHelper::MIN_LOG_LEVEL] ?? LoggerHelper::UNKNOWN_LOG_LEVEL
            );
        }
    }

    /**
     * Function used to register logger to a file system log handler
     * @param Logger $logger
     * @param string $channelName
     * @param string $minLevel
     */
    protected function registerLoggerToFileSystem(Logger $logger, string $channelName, string $minLevel)
    {
        $fileName = storage_path(
            implode(DIRECTORY_SEPARATOR, [
                "logs",
                "{$channelName}.log"
            ])
        );

        $handler = new RotatingFileHandler(
            $fileName,
            0,
            $this->computeLoggerSeverityLevel($minLevel)
        );

        $handler->setFormatter(new LineFormatter(null, null, true, true));

        $logger->pushHandler($handler);
    }

    /**
     * Function used to return the integer equivalent for a severity level
     * @param string $level
     * @return int
     */
    protected function computeLoggerSeverityLevel(string $level): int
    {
        return LoggerHelper::LEVELS[$level] ?? Logger::DEBUG;
    }
}