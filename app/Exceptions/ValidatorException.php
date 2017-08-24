<?php
/**
 * Created by PhpStorm.
 * User: chase
 * Date: 02/02/17
 * Time: 13:50
 */

namespace App\Exceptions;


use App\Interfaces\DefaultValidator;

class ValidatorException extends DefaultException
{

    const INVALID_VALIDATOR_CLASS_GIVEN = "Invalid validator class given ";
    const VALIDATOR_DOES_NOT_HAVE_VALIDATE_FUNCTION =
        "Method '" .
        DefaultValidator::FUNCTION_CALLED_TO_VALIDATE_DATA .
        "' does not exist in validator";
    const FIELD_NOT_ALLOWED = "Invalid field in array structure";
    const VALIDATOR_TYPE_NOT_FOUND = "Invalid validation type received";
    const VALIDATION_FAILED_FOR_FIELD = "Validation failed for value '%s'. Must be of type '%s' ";

    public function report()
    {
        /* Do not report for validation */
        return;
    }
}