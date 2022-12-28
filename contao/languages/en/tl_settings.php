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

// Legends
$GLOBALS['TL_LANG']['tl_settings']['log_report_legend'] = 'Log-Report';

// Fields
$GLOBALS['TL_LANG']['tl_settings']['log_report_activate'] = ['Activate Log-Report', 'Here you can activate Log Report.'];
$GLOBALS['TL_LANG']['tl_settings']['log_report_recipients'] = ['Email-Recipients:', 'Please enter a comma-separated-list with email addresses.'];
$GLOBALS['TL_LANG']['tl_settings']['log_report_observed_tables'] = ['Observed tables', 'Select the tables which you want to have been observed.'];
$GLOBALS['TL_LANG']['tl_settings']['log_report_additional_observed_tables'] = ['Custom tables', 'Add a comma-separated list containing tables, which you want have to be observed. (tl_table1,tl_table2,...)'];
$GLOBALS['TL_LANG']['tl_settings']['log_report_send_email_when_db_changed'] = ['Only send email, when database has changed', 'The system will not send a message to you, if there are no changes in the database.'];
$GLOBALS['TL_LANG']['tl_settings']['log_report_template'] = ['Template-settings', 'Choose a custom template.'];
