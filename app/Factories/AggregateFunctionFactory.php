<?php
/**
 * Created by PhpStorm.
 * User: chase
 * Date: 15/05/17
 * Time: 17:29
 */

namespace App\Factories;


use App\Models\AggregateFunctions\DefaultFunction;

class AggregateFunctionFactory
{
    /**
     * The fully qualified namespace for ReportingTable classes
     * @var string
     */
    private static $namespace = "App\\Models\\AggregateFunctions\\";

    /**
     * Builder for factory
     * @param $type
     * @return DefaultFunction
     * @throws \Exception
     */
    public static function build($type)
    {
        $className = ucfirst(camel_case($type));

        $aggregateFunctionClass = self::$namespace . $className;

        if (class_exists($aggregateFunctionClass)) {
            return new $aggregateFunctionClass();
        }

        throw new \Exception("Invalid aggregate function type. Given $type");
    }


}