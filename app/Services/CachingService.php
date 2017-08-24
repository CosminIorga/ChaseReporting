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
            Data::INTERVAL_START,
            Data::INTERVAL_END,
            Data::COLUMNS,
            Data::GROUP_CLAUSE,
            Data::WHERE_CLAUSE,
        ]));

        /* Order data in order to maintain consistency across requests with parameters in different order */

        /* Order columns by column_alias */
        usort($reducedData[Data::COLUMNS], function (array $column1, array $column2) {
            return strcmp($column1[Data::COLUMN_ALIAS], $column2[Data::COLUMN_ALIAS]);
        });

        /* Order groupBy clause */
        sort($reducedData[Data::GROUP_CLAUSE]);

        /* Order where clause */
        usort($reducedData[Data::WHERE_CLAUSE], function (array $whereCondition1, array $whereCondition2) {
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