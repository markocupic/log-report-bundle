<?php

declare(strict_types=1);

/*
 * This file is part of Log Report Bundle.
 *
 * (c) Marko Cupic 2022 <m.cupic@gmx.ch>
 * @license GPL-3.0-or-later
 * For the full copyright and license information,
 * please view the LICENSE file that was distributed with this source code.
 * @link https://github.com/markocupic/log-report-bundle
 */

$GLOBALS['TL_DCA']['tl_log_report'] = [
    'config' => [
        'dataContainer' => 'Table',
        'sql'           => [
            'keys' => [
                'id' => 'primary',
            ],
        ],
    ],
    'fields' => [
        'id'         => [
            'sql' => 'int(10) unsigned NOT NULL auto_increment',
        ],
        'date'       => [
            'sql' => "varchar(10) NOT NULL default ''",
        ],
        'recipients' => [
            'sql' => "text NOT NULL default ''",
        ],
        'report'     => [
            'sql' => "text NOT NULL default ''",
        ],
    ],
];
