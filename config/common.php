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
     * Allowed options are stored in App\Definitions\Common.php
     */
    'data_interval' => 60,


    /**
     * The interval the table should store data.
     * Allowed options are stored in App\Definitions\Common.php
     */
    'table_interval' => 'daily',


    /**
     * Flag used to detect whether parallel processing for certain features is enabled such as data fetching
     */
    'gearman_parallel_processing' => false,


    /**
     * Flag used to determine if data operations (insert / delete / update) are instantly processed
     * or should be queued and executed at certain intervals.
     * Allowed options are any integer value between 1 (1 minute) and 1440 (24 hours) or false.
     * Numeric values represent the interval between each batch processing in minutes.
     */
    'batch_processing' => false,
];