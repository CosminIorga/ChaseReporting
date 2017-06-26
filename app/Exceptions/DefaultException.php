<?php
/**
 * Created by PhpStorm.
 * User: chase
 * Date: 26/06/17
 * Time: 15:13
 */

namespace App\Exceptions;


use App\Interfaces\DefaultException as DefaultExceptionTrait;
use Exception;

/**
 * Class DefaultException
 * @package App\Exceptions
 */
abstract class DefaultException extends \Exception implements DefaultExceptionTrait
{
    /**
     * Array used to store the context of an exception
     * @var array
     */
    protected $context = [];

    /**
     * DefaultException constructor.
     * @param string $message
     * @param array $context
     * @param int $code
     */
    public function __construct(string $message, array $context = [], $code = 0)
    {
        $this->init($context);

        parent::__construct($message, $code, null);

        $this->report();
    }

    /**
     * Short function used to initialize various fields
     * @param array $context
     */
    protected function init(array $context)
    {
        $this->context = $context;
    }

    /**
     * Getter for context
     * @return array
     */
    public function getContext(): array
    {
        return $this->context;
    }

    /**
     * Function should be implemented by all children exceptions
     */
    abstract public function report();
}