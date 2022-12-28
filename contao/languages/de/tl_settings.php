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
$GLOBALS['TL_LANG']['tl_settings']['log_report_activate'] = ['Log-Report aktivieren', 'Hier können Sie Log Report aktivieren.'];
$GLOBALS['TL_LANG']['tl_settings']['log_report_recipients'] = ['E-Mail-Empfänger', 'Bitte geben Sie eine kommaseparierte Liste mit E-Mail-Adressen an.'];
$GLOBALS['TL_LANG']['tl_settings']['log_report_observed_tables'] = ['Überwachte Tabellen', 'Wählen Sie die Tabellen aus, die überwacht werden sollen.'];
$GLOBALS['TL_LANG']['tl_settings']['log_report_additional_observed_tables'] = ['Zusätzlich zu überwachende Tabellen', 'Erstellen Sie hier eine kommaseparierte Liste mit Tabellen, die Sie überwacht haben möchten. (tl_table1,tl_table2,...)'];
$GLOBALS['TL_LANG']['tl_settings']['log_report_send_email_when_db_changed'] = ['Nur bei Änderungen benachrichtigen', 'Wenn keine Änderungen vorhanden sind, wird bei aktivierter Checkbox das System den Empfänger nicht benachrichtigen.'];
$GLOBALS['TL_LANG']['tl_settings']['log_report_template'] = ['Template-Einstellungen', 'Wählen Sie ein Template aus.'];
