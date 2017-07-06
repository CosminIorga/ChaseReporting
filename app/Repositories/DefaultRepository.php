<?php
/**
 * Created by PhpStorm.
 * User: chase
 * Date: 08/06/17
 * Time: 11:55
 */

namespace App\Repositories;

use App\Traits\Common;
use Illuminate\Database\Connection;
use Illuminate\Database\Query\Builder;
use Illuminate\Database\Schema\MySqlBuilder;

abstract class DefaultRepository
{
    use Common;

    /**
     * Function used to return the Schema builder for the config connection
     * @return MySqlBuilder
     */
    protected function getConfigSchema(): MySqlBuilder
    {
        /* @noinspection PhpUndefinedMethodInspection */
        return \Schema::connection('config_connection');
    }

    /**
     * Function used to return the Query builder for the config connection
     * @return Connection
     */
    protected function getConfigConnection(): Connection
    {
        return \DB::connection('config_connection');
    }

    /**
     * Function used to return the Schema builder for the data connection
     * @return MySqlBuilder
     */
    protected function getDataSchema(): MySqlBuilder
    {
        /* @noinspection PhpUndefinedMethodInspection */
        return \Schema::connection('data_connection');
    }

    /**
     * Function used to return the Query builder for the data connection
     * @return Connection
     */
    protected function getDataConnection(): Connection
    {
        return \DB::connection('data_connection');
    }


    /**
     * Function used to initialize the query builder
     * @return Builder
     */
    abstract protected function initQueryBuilder(): Builder;
}