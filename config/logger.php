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
    'default_channel' => [
        'min_level' => 'info',
        'mediums' => [
            'registerToFileSystem' => true,
        ]
    ],

    'another_channel' => [
        'min_level' => 'warning',
        'mediums' => [
            'registerToFileSystem' => true,
        ]
    ],
];