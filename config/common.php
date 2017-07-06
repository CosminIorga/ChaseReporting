<?php
/**
 * Created by PhpStorm.
 * User: chase
 * Date: 14/04/17
 * Time: 11:57
 */

return [

    /**
     * The interval stored in the database (in minutes).
     * Available options are stored in App\Definitions\Common.php
     */
    'data_interval' => 60,


    /**
     * The interval the table should store data.
     * Available options are stored in App\Definitions\Common.php
     * If 'daily' option is selected but interval is less than 60, it will trigger a MySQL error of too many columns
     */
    'table_interval' => 'daily',


    /**
     * Flag used to detect whether parallel processing for certain features is enabled such as data fetching
     */
    'gearman_parallel_processing' => true
];