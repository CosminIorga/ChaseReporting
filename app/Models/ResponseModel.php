<?php
/**
 * Created by PhpStorm.
 * User: chase
 * Date: 02/03/17
 * Time: 11:27
 */

namespace App\Models;

/**
 * Class ResponseModel
 * @package App\Models
 */
class ResponseModel
{


    /**
     * Status of response (Either true = success or false = failure)
     * @var bool
     */
    protected $status = false;

    /**
     * Message of response, regardless of response status
     * @var string
     */
    protected $message = '';

    /**
     * Various content related to the response
     * @var mixed
     */
    protected $content = null;

    /**
     * ResponseModel constructor.
     * @param bool $status
     * @param string $message
     * @param $content
     */
    public function __construct(bool $status = false, string $message = '', $content = null)
    {
        $this->status = $status;
        $this->message = $message;
        $this->content = $content;
    }

    /**
     * Setter for status
     * @param $status
     * @return ResponseModel
     */
    public function status(bool $status): self
    {
        $this->status = $status;

        return $this;
    }

    /**
     * Getter for status
     * @return bool
     */
    public function getStatus(): bool
    {
        return $this->status;
    }

    /**
     * Function used to set response message
     * @param string $message
     * @return ResponseModel
     */
    public function message(string $message): self
    {
        $this->message = $message;

        return $this;
    }

    /**
     * Getter for message
     * @return string
     */
    public function getMessage(): string
    {
        return $this->message;
    }

    /**
     * Function used to set contents of response
     * @param mixed $content
     * @return ResponseModel
     */
    public function content($content): self
    {
        $this->content = $content;

        return $this;
    }

    /**
     * Getter for content
     * @return mixed
     */
    public function getContent()
    {
        return $this->content;
    }
}