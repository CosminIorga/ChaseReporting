<?php
/**
 * Created by PhpStorm.
 * User: chase
 * Date: 25/04/17
 * Time: 12:03
 */

namespace App\Factories;


use App\Models\ReportingTables\ReportingTable;

class ReportingTableFactory
{
    /**
     * The fully qualified namespace for ReportingTable classes
     * @var string
     */
    private static $namespace = "App\\Models\\ReportingTables\\";

    /**
     * Builder for factory
     * @param $type
     * @return ReportingTable
     * @throws \Exception
     */
    public static function build($type)
    {
        $className = ucfirst(camel_case($type));

        $reportingTableClass = self::$namespace . $className;

        if (class_exists($reportingTableClass)) {
            return new $reportingTableClass();
        }

        throw new \Exception("Invalid reporting table type given");
    }
}