<?php
/**
 * Created by PhpStorm.
 * User: chase
 * Date: 23/06/17
 * Time: 15:26
 */

/**
 * All logger channel configuration resides in this array
 * Each channel name must define the "mediums" array and it should not be empty
 */
return [
    /**
     * This is the default channel used for general logging
     */
    'default_channel' => [
        'min_level' => 'info',
        'mediums' => [
            'registerToFileSystem' => true,
        ]
    ],

    /**
     * Channel used exclusively for logs related to reporting table creation
     */
    'create_reporting_table' => [
        'min_level' => 'debug',
        'mediums' => [
            'registerToFileSystem' => true,
        ]
    ],

    /**
     * Channel used exclusively for logs related to reporting table data modification
     */
    'alter_data' => [
        'min_level' => 'debug',
        'mediums' => [
            'registerToFileSystem' => true,
        ]
    ],

    /**
     * Channel used exclusively for logs related to data fetching from reporting tables
     */
    'fetch_data' => [
        'min_level' => 'debug',
        'mediums' => [
            'registerToFileSystem' => true,
        ]
    ],

    /**
     * Channel used to log information regarding the gearman workers, client and associated processes
     */
    'gearman' => [
        'min_level' => 'debug',
        'mediums' => [
            'registerToFileSystem' => true
        ]
    ]
];