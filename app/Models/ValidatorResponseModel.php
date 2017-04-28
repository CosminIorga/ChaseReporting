<?php

namespace App\Models;


class ValidatorResponseModel
{

    protected $status = false;
    protected $validationErrors = [];

    /**
     * ValidatorResponseModel constructor.
     * @param bool $status
     * @param array $validationErrors
     */
    public function __construct(bool $status, array $validationErrors = [])
    {
        $this->status = $status;
        $this->validationErrors = $validationErrors;
    }

    /**
     * Setter for status
     * @param $status
     * @return ValidatorResponseModel
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
     * Internal function used to set errors
     * @param array $errors
     * @return ValidatorResponseModel
     */
    public function validationErrors(array $errors): self
    {
        $this->validationErrors = $errors;

        return $this;
    }

    /**
     * Function used to retrieve all validation errors
     * @return array
     */
    public function getValidationErrors(): array
    {
        return $this->validationErrors;
    }
}
