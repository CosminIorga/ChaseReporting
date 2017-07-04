<?php
/**
 * Created by PhpStorm.
 * User: chase
 * Date: 03/07/17
 * Time: 12:41
 */

namespace App\Services;


use App\Definitions\Data;

class CachingService
{
    /**
     * Instantiator for
     * @return CachingService
     */
    public static function Instance()
    {
        static $inst = null;

        if ($inst === null) {
            $inst = new CachingService();
        }

        return $inst;
    }

    /**
     * CachingService constructor.
     */
    private function __construct()
    {
    }

    /**
     * Function used to compute a cache key based on given fetch data
     * @param array $fetchData
     * @return string
     */
    public function computeCacheKeyFromFetchData(array $fetchData): string
    {
        /* Take only necessary keys for caching */
        $reducedData = array_intersect_key($fetchData, array_flip([
            Data::FETCH_INTERVAL_START,
            Data::FETCH_INTERVAL_END,
            Data::FETCH_COLUMNS,
            Data::FETCH_GROUP_CLAUSE,
            Data::FETCH_WHERE_CLAUSE
        ]));

        /* Order data in order to maintain consistency across requests with parameters in different order */

        /* Order columns by key */
        ksort($reducedData[Data::FETCH_COLUMNS]);

        /* Order column functions by name */
        foreach ($reducedData[Data::FETCH_COLUMNS] as &$columnInfo) {
            ksort($columnInfo);

            /* Order column extra clause */
            foreach ($columnInfo as &$extraConfig) {
                ksort($extraConfig);
            }
        }

        /* Order groupBy clause */
        sort($reducedData[Data::FETCH_GROUP_CLAUSE]);

        /* Order where clause */
        usort($reducedData[Data::FETCH_WHERE_CLAUSE], function (array $whereCondition1, array $whereCondition2) {
            return serialize($whereCondition1) < serialize($whereCondition2);
        });

        $cacheKey = base64_encode(serialize($reducedData));

        return $cacheKey;
    }

    /**
     * Function used to decode a string from cache
     * @param string $encodedData
     * @return array
     */
    public function decodeCacheData(string $encodedData): array
    {
        return json_decode($encodedData, true);
    }

    /**
     * Function used to encode a string for cache
     * @param array $decodedData
     * @return string
     */
    public function encodeCacheData(array $decodedData): string
    {
        return json_encode($decodedData);
    }

}