<?php
/**
 * Created by PhpStorm.
 * User: chase
 * Date: 04/07/17
 * Time: 14:57
 */

namespace App\Definitions;


class Gearman
{
    /**
     * Available gearman task names
     */
    const FETCH_TASK = 'fetch_task';

    /**
     * Constant array used to map available tasks to functions
     */
    const GEARMAN_FUNCTION_MAPPING = [
        Gearman::FETCH_TASK => 'fetchDataUsingGearmanNode'
    ];

    /**
     * Constants used to easily access information
     */
    const WORKER_ID = 'worker_id';

}