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

use Contao\CoreBundle\DataContainer\PaletteManipulator;

PaletteManipulator::create()
    ->addLegend('log_report_legend', 'default', PaletteManipulator::POSITION_APPEND)
    ->addField(['log_report_activate', 'log_report_observed_tables', 'log_report_additional_observed_tables', 'log_report_send_email_when_db_changed', 'log_report_recipients', 'log_report_template'], 'log_report_legend', PaletteManipulator::POSITION_APPEND)
    ->applyToPalette('default', 'tl_settings');

/*
 * Add fields
 */
$GLOBALS['TL_DCA']['tl_settings']['fields']['log_report_activate'] = [
    'inputType' => 'checkbox',
    'default'   => 'true',
    'eval'      => ['tl_class' => 'long clr'],
];

$GLOBALS['TL_DCA']['tl_settings']['fields']['log_report_send_email_when_db_changed'] = [
    'inputType' => 'checkbox',
    'default'   => 'true',
    'eval'      => ['tl_class' => 'long clr'],
];

$GLOBALS['TL_DCA']['tl_settings']['fields']['log_report_template'] = [
    'inputType' => 'select',
    'eval'      => ['tl_class' => 'long clr'],
];

$GLOBALS['TL_DCA']['tl_settings']['fields']['log_report_recipients'] = [
    'inputType' => 'text',
    'eval'      => ['tl_class' => 'long clr'],
];

$GLOBALS['TL_DCA']['tl_settings']['fields']['log_report_observed_tables'] = [
    'inputType' => 'checkbox',
    'options'   => ['tl_content', 'tl_member', 'tl_news', 'tl_user'],
    'eval'      => ['multiple' => true, 'tl_class' => 'long clr'],
];

$GLOBALS['TL_DCA']['tl_settings']['fields']['log_report_additional_observed_tables'] = [
    'inputType' => 'text',
    'eval'      => ['tl_class' => 'long clr'],
];
