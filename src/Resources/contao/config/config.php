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

use Markocupic\LogReportBundle\Util\ContaoMaintenance;

$GLOBALS['TL_PURGE']['tables']['log_report']['callback'] = [
    ContaoMaintenance::class,
    'purgeLogReportTable',
];

$GLOBALS['TL_PURGE']['tables']['log_report']['affected'] = ['tl_log_report'];
