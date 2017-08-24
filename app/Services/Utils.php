<?php
/**
 * Created by PhpStorm.
 * User: chase
 * Date: 14/08/17
 * Time: 15:52
 */

namespace App\Services;


class Utils
{

    /**
     * Small function used to quote a string
     * @param string $string
     * @return string
     */
    static public function quote(string $string): string
    {
        return "'{$string}'";
    }


    static public function transformToMySQLOutput(array $data)
    {
        $first = true;

        $output = "";

        $spaces = [];

        foreach ($data as $record) {
            foreach ($record as $header => $value) {
                $spaces[$header] = max(strlen($header), strlen($value));
            }
        }

        foreach ($data as $record) {
            /* Show headers if first */
            if ($first) {
                $headers = array_map(function (string $header) use ($spaces) {
                    return str_pad($header, $spaces[$header], " ", STR_PAD_LEFT);
                }, array_keys((array) $record));

                $output .= implode(" | ", $headers) . PHP_EOL;


                $pluses = array_map(function (string $header) use ($spaces) {
                    return str_pad("", $spaces[$header], "-", STR_PAD_BOTH);
                }, array_keys((array) $record));

                $output .= implode('-+-', $pluses);

                $output .= PHP_EOL;

                $first = false;
            }


            $values = array_map(function (string $value, $header) use ($spaces) {
                return str_pad($value, $spaces[$header], " ", STR_PAD_LEFT);
            }, (array) $record, array_keys((array) $record));

            $output .= implode(' | ', $values) . PHP_EOL;
        }

        echo $output;
    }
}