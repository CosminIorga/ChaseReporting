<?php
/**
 * Created by PhpStorm.
 * User: chase
 * Date: 23/06/17
 * Time: 12:56
 */

namespace App\Services;


use App\Definitions\Logger;
use App\Helpers\ChannelWriterHelper;
use InvalidArgumentException;

/**
 * Class ChannelWriter
 * @package App\Helpers
 */
class ChannelWriter extends ChannelWriterHelper
{
    /**
     * The Log channels.
     * @var array
     */
    protected $channels = [];

    /**
     * The Log levels.
     * @var array
     */
    protected $levels = [];

    /**
     * ChannelWriter constructor.
     */
    public function __construct()
    {
        $this->init();
    }

    /**
     * Short function used to initialize various fields
     */
    protected function init()
    {
        /* Set channels */
        $this->channels = ConfigGetter::Instance()->loggerChannels;

        /* Set levels */
        $this->levels = Logger::LEVELS;
    }

    /**
     * Write to log based on the given channel and log level set
     * @param string $channelName
     * @param string $level
     * @param string $message
     * @param array $context
     */
    public function writeLog(string $channelName, string $level, string $message, array $context = [])
    {
        /* Check if channel exists */
        if (!in_array($channelName, array_keys($this->channels))) {
            throw new InvalidArgumentException('Invalid channel used.');
        }

        /* Create channel instances if not defined */
        if (!isset($this->channels[$channelName][Logger::LOGGER_INSTANCE])) {
            /* This parse registering */
            $this->parseRegistering($this->channels[$channelName], $channelName);
        }

        /* Write out messages */
        $this->channels[$channelName][Logger::LOGGER_INSTANCE]->{$level}($message, $context);
    }

    /**
     * Broadcast message at "debug" severity
     * @param string $channelName
     * @param string $message
     * @param array $context
     */
    public function debug(string $channelName, string $message, array $context)
    {
        $this->writeLog($channelName, 'debug', $message, $context);
    }

    /**
     * Broadcast message at "info" severity
     * @param string $channelName
     * @param string $message
     * @param array $context
     */
    public function info(string $channelName, string $message, array $context)
    {
        $this->writeLog($channelName, 'info', $message, $context);
    }

    /**
     * Broadcast message at "notice" severity
     * @param string $channelName
     * @param string $message
     * @param array $context
     */
    public function notice(string $channelName, string $message, array $context)
    {
        $this->writeLog($channelName, 'notice', $message, $context);
    }

    /**
     * Broadcast message at "warning" severity
     * @param string $channelName
     * @param string $message
     * @param array $context
     */
    public function warning(string $channelName, string $message, array $context)
    {
        $this->writeLog($channelName, 'warning', $message, $context);
    }

    /**
     * Broadcast message at "error" severity
     * @param string $channelName
     * @param string $message
     * @param array $context
     */
    public function error(string $channelName, string $message, array $context)
    {
        $this->writeLog($channelName, 'error', $message, $context);
    }

    /**
     * Broadcast message at "critical" severity
     * @param string $channelName
     * @param string $message
     * @param array $context
     */
    public function critical(string $channelName, string $message, array $context)
    {
        $this->writeLog($channelName, 'critical', $message, $context);
    }

    /**
     * Broadcast message at "alert" severity
     * @param string $channelName
     * @param string $message
     * @param array $context
     */
    public function alert(string $channelName, string $message, array $context)
    {
        $this->writeLog($channelName, 'alert', $message, $context);
    }

    /**
     * Broadcast message at "emergency" severity
     * @param string $channelName
     * @param string $message
     * @param array $context
     */
    public function emergency(string $channelName, string $message, array $context)
    {
        $this->writeLog($channelName, 'emergency', $message, $context);
    }
}