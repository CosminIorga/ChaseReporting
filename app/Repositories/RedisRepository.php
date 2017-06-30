<?php
/**
 * Created by PhpStorm.
 * User: chase
 * Date: 27/06/17
 * Time: 10:45
 */

namespace App\Repositories;


use Illuminate\Support\Facades\Redis;
use Predis\Response\Status;


class RedisRepository
{


    /**
     * Function used to retrieve value from Redis given a key
     * @param string $key
     * @return string|null
     */
    public function get(string $key)
    {
        /** @noinspection PhpUndefinedClassInspection */
        /** @noinspection PhpUndefinedMethodInspection */
        return Redis::get($key);
    }

    /**
     * Function used to set a value in Redis given a key
     * @param string $key
     * @param string $value
     * @return mixed
     */
    public function set(string $key, string $value): Status
    {
        /** @noinspection PhpUndefinedClassInspection */
        /** @noinspection PhpUndefinedMethodInspection */
        return Redis::set($key, $value);
    }


    /**
     * Function used to delete a value from Redis given a key
     * @param string $key
     * @return int
     */
    public function delete(string $key): int
    {
        /** @noinspection PhpUndefinedClassInspection */
        /** @noinspection PhpUndefinedMethodInspection */
        return Redis::del($key);
    }

}