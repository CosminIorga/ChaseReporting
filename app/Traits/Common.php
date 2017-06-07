<?php
/**
 * Created by PhpStorm.
 * User: chase
 * Date: 16/05/17
 * Time: 18:02
 */

namespace App\Traits;


use stdClass;

trait Common
{

    /**
     * Short function used to convert a stdObject to array
     * @param stdClass $value
     * @return array
     */
    public function transformStdObjectToArray($value): array
    {
        return json_decode(json_encode($value), true) ?? [];
    }

    /**
     * Function used to flatten an array but maintain keys
     * @param array $array
     * @return array
     */
    public function flattenArray(array $array): array
    {
        $iterator = new \RecursiveIteratorIterator(new \RecursiveArrayIterator($array));

        $returnData = [];

        foreach ($iterator as $key => $value) {
            $returnData[$key] = $value;
        }

        return $returnData;
    }
}