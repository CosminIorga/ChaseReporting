<?php
/**
 * Created by PhpStorm.
 * User: chase
 * Date: 26/06/17
 * Time: 14:46
 */

namespace App\Traits;


trait LogHelper
{
    /**
     * Channel name used when logging
     * @var string
     */
    protected $logChannel = 'default_channel';

    /**
     * Function used to specify the channel the logger will use
     * @param string $channel
     */
    public function setChannel(string $channel)
    {
        $this->logChannel = $channel;
    }

    /**
     * Broadcast message at "debug" severity
     * @param string $message
     * @param array $context
     */
    public function debug(string $message, array $context = [])
    {
        \ChannelLog::debug($this->logChannel, $message, $context);
    }

    /**
     * Broadcast message at "info" severity
     * @param string $message
     * @param array $context
     */
    public function info(string $message, array $context = [])
    {
        \ChannelLog::info($this->logChannel, $message, $context);
    }

    /**
     * Broadcast message at "notice" severity
     * @param string $message
     * @param array $context
     */
    public function notice(string $message, array $context = [])
    {
        \ChannelLog::notice($this->logChannel, $message, $context);
    }

    /**
     * Broadcast message at "warning" severity
     * @param string $message
     * @param array $context
     */
    public function warning(string $message, array $context = [])
    {
        \ChannelLog::warning($this->logChannel, $message, $context);
    }

    /**
     * Broadcast message at "error" severity
     * @param string $message
     * @param array $context
     */
    public function error(string $message, array $context = [])
    {
        \ChannelLog::error($this->logChannel, $message, $context);
    }

    /**
     * Broadcast message at "critical" severity
     * @param string $message
     * @param array $context
     */
    public function critical(string $message, array $context = [])
    {
        \ChannelLog::critical($this->logChannel, $message, $context);
    }

    /**
     * Broadcast message at "alert" severity
     * @param string $message
     * @param array $context
     */
    public function alert(string $message, array $context = [])
    {
        \ChannelLog::alert($this->logChannel, $message, $context);
    }

    /**
     * Broadcast message at "emergency" severity
     * @param string $message
     * @param array $context
     */
    public function emergency(string $message, array $context = [])
    {
        \ChannelLog::emergency($this->logChannel, $message, $context);
    }

}