<?php
/**
 * Created by PhpStorm.
 * User: chase
 * Date: 01/03/17
 * Time: 16:40
 */

namespace App\Traits;


trait ModelValidator
{
    /**
     * Validation rules
     * @var array
     */
    protected $rules = [];

    /**
     * Array used to store errors that appear during model creation and logic
     * @var array
     */
    protected $errors = [];

    /**
     * Variable used to trigger an exception if data validation fails
     * @var bool
     */
    protected $throwExceptionOnValidationFail = false;

    /**
     * Function called to validate $data
     * @param array $data
     * @throws \Exception
     */
    public function validate($data)
    {
        /* Create new validator object */
        $v = \Validator::make($data, $this->rules);

        /* Check if validation passes */
        if ($v->fails()) {
            $this->errors = $v->errors()->all();

            throw new \Exception("Following errors occurred: " . PHP_EOL . implode(PHP_EOL, $this->errors));
        }
    }

    /**
     * Function used to check if model has errors
     * @return bool
     */
    public function hasErrors(): bool
    {
        return !empty($this->errors);
    }

    /**
     * Function used to retrieve model errors
     * @return array
     */
    public function getErrors(): array
    {
        return $this->errors;
    }
}