<?php
/**
 * Created by PhpStorm.
 * User: chase
 * Date: 01/03/17
 * Time: 17:17
 */

namespace App\Models\BaseModels;


use App\Traits\ModelValidator;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Contracts\Support\Jsonable;
use Illuminate\Support\Str;

/**
 * Class NonPersistentModel
 * @package App\Models
 */
class NonPersistentModel implements Arrayable, Jsonable
{
    use ModelValidator;

    /**
     * Variable used to store model's attributes
     * @var array
     */
    protected $attributes = [];

    /**
     * Variable used to store default values for attributes
     * @var array
     */
    protected $defaultAttributeValues = [];

    /**
     * Holds an list of getter methods, in order to avoid creating them every time
     * @var array
     */
    protected $getterCache = [];

    /**
     * Holds an list of setter methods, in order to avoid creating them every time
     * @var array
     */
    protected $setterCache = [];


    /**
     * NonPersistentModel constructor.
     * @param array $attributes
     */
    public function __construct(array $attributes)
    {
        array_walk($this->defaultAttributeValues, function ($defaultValue, $attribute) use (&$attributes) {
            if (!array_key_exists($attribute, $attributes)) {
                $attributes[$attribute] = $defaultValue;
            }
        });

        $this->initBefore($attributes);

        $this->setAttributes($attributes);

        $this->initAfter($attributes);
    }

    /**
     * Function called before validating input data and filling attributes
     * @param array $attributes
     */
    protected function initBefore(array $attributes)
    {
    }

    /**
     * Function called after validating input data and filling attributes
     * @param array $attributes
     */
    protected function initAfter(array $attributes)
    {
    }

    /**
     * Set model attribute to given value
     * @param string $attribute
     * @param mixed $value
     * @return NonPersistentModel
     */
    public function setAttribute(string $attribute, $value): self
    {
        $this->setAttributeWithoutValidation($attribute, $value);

        $this->validate($this->getAttributes());

        return $this;
    }

    /**
     * Function used to set model attribute without validating data after
     * @param string $attribute
     * @param mixed $value
     */
    protected function setAttributeWithoutValidation(string $attribute, $value)
    {
        switch (true) {
            case $this->hasSetter($attribute):
                $method = $this->createSetter($attribute);

                $this->attributes[$attribute] = $this->{$method}($value);

                break;
            default:
                $this->attributes[$attribute] = $value;
        }
    }

    /**
     * Set multiple model attributes
     * @param array $attributes
     * @return NonPersistentModel
     */
    public function setAttributes(array $attributes): self
    {
        array_walk($attributes, function ($value, $attribute) {
            $this->setAttributeWithoutValidation($attribute, $value);
        });

        $this->validate($this->getAttributes());

        return $this;
    }

    /**
     * Get model attribute
     * @param string $key
     * @return mixed
     */
    public function getAttribute(string $key)
    {
        $value = $this->attributes[$key] ?? null;;

        if ($this->hasGetter($key)) {
            $method = $this->createGetter($key);

            return $this->{$method}($value);
        }

        return $value;
    }

    /**
     * Get model attributes
     * @return array
     */
    public function getAttributes(): array
    {
        $attributes = [];

        foreach (array_keys($this->attributes) as $attribute) {
            $attributes[$attribute] = $this->getAttribute($attribute);
        }

        return $attributes;
    }

    /**
     * Generates the getter.
     * @param string $key
     * @return string
     */
    protected function createGetter(string $key): string
    {
        if (!array_key_exists($key, $this->getterCache)) {
            $this->getterCache[$key] = 'get' . Str::studly($key) . 'Attribute';
        }

        return $this->getterCache[$key];
    }

    /**
     * Generates the setter.
     * @param string $key
     * @return string
     */
    protected function createSetter(string $key): string
    {
        if (!array_key_exists($key, $this->setterCache)) {
            $this->setterCache[$key] = 'set' . Str::studly($key) . 'Attribute';
        }

        return $this->setterCache[$key];
    }

    /**
     * Determine if a getter exists for an attribute.
     * @param string $key
     * @return bool
     */
    public function hasGetter(string $key): bool
    {
        return method_exists($this, $this->createGetter($key));
    }

    /**
     * Determine if a setter exists for an attribute.
     * @param string $key
     * @return bool
     */
    public function hasSetter($key): bool
    {
        return method_exists($this, $this->createSetter($key));
    }

    /**
     * Get the instance as an array.
     * @return array
     */
    public function toArray(): array
    {
        $array = [];

        /* Iterate over attributes (keys) */
        $keys = array_keys($this->getAttributes());

        array_walk($keys, function ($attribute) use (&$array) {
            $value = $this->getAttribute($attribute);

            if (is_object($value)) {
                if (method_exists($value, 'toArray') || $value instanceof Arrayable) {
                    $array[$attribute] = $value->toArray();

                    return;
                }

                $array[$attribute] = (array) $value;

                return;
            }

            $array[$attribute] = $value;
        });

        return $array;
    }

    /**
     * Convert the object to its JSON representation.
     * @param int $options
     * @return string
     */
    public function toJson($options = 0): string
    {
        return json_encode($this->toArray());
    }

    /**
     * Dynamically retrieve attributes on the model.
     * @param string $key
     * @return mixed
     */
    public function __get($key)
    {
        return $this->getAttribute($key);
    }
}