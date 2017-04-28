<?php
/**
 * Created by PhpStorm.
 * User: chase
 * Date: 14/04/17
 * Time: 14:45
 */

namespace Validators;


use App\Interfaces\DefaultValidator;
use App\Models\ValidatorResponseModel;

class DataValidator implements DefaultValidator
{


    /**
     * Array used to hold the data to validate
     * @var array
     */
    protected $data;

    /**
     * Array containing validation rules
     * @var array
     */
    protected $rules;

    /**
     * DataValidator constructor.
     * @param array $data
     * @param array $rules
     */
    public function __construct(array $data, array $rules)
    {
        $this->init($data, $rules);
    }

    /**
     * Function used to initialize fields
     * @param array $data
     * @param array $rules
     */
    protected function init(array $data, array $rules)
    {
        $this->data = $data;
        $this->rules = $rules;
    }

    /**
     * Function called to validate data
     * @return ValidatorResponseModel
     */
    public function validate(): ValidatorResponseModel
    {
        $v = \Validator::make($this->data, $this->rules);

        /* Check if validation passes */
        if ($v->fails()) {
            $errors = $v->errors()->all();

            return (new ValidatorResponseModel(false, $errors));
        }

        return (new ValidatorResponseModel(true));
    }
}