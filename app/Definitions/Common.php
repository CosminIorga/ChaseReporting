<?php
/**
 * Created by PhpStorm.
 * User: chase
 * Date: 14/04/17
 * Time: 15:45
 */

namespace App\Definitions;


class Common
{
    /**
     * Data intervals (In minutes)
     */
    const DATA_INTERVAL_15_MINUTES = 15;
    const DATA_INTERVAL_30_MINUTES = 30;
    const DATA_INTERVAL_60_MINUTES = 60;
    const DATA_INTERVAL_120_MINUTES = 120;

    const AVAILABLE_DATA_INTERVALS = [
        self::DATA_INTERVAL_15_MINUTES,
        self::DATA_INTERVAL_30_MINUTES,
        self::DATA_INTERVAL_60_MINUTES,
        self::DATA_INTERVAL_120_MINUTES,
    ];


    /**
     * Table intervals
     */
    const TABLE_INTERVAL_QUARTER = 'quarter_day';
    const TABLE_INTERVAL_HALF = 'half_day';
    const TABLE_INTERVAL_DAILY = 'daily';
    const TABLE_INTERVAL_WEEKLY = 'weekly';
    const TABLE_INTERVAL_MONTHLY = 'monthly';

    const AVAILABLE_TABLE_INTERVALS = [
        self::TABLE_INTERVAL_QUARTER,
        self::TABLE_INTERVAL_HALF,
        self::TABLE_INTERVAL_DAILY,
        //self::TABLE_INTERVAL_WEEKLY,
        //self::TABLE_INTERVAL_MONTHLY,
        //TODO: IMPLEMENT weekly and monthly reporting tables
    ];
}