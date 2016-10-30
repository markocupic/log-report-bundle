<?php

/**
 * Contao Open Source CMS
 *
 * Copyright (c) 2005-2014 Leo Feyer
 *
 * @package   log_report
 * @author    Marko Cupic
 * @license   shareware
 * @copyright Marko Cupic 2014
 */


$GLOBALS['TL_PURGE']['tables']['log_report']['callback'] = array(
       'MCupic\LogReport',
       'purgeLogReportTable'
);
$GLOBALS['TL_PURGE']['tables']['log_report']['affected'] = array('tl_log_report');

$GLOBALS['TL_HOOKS']['generatePage'][] = array(
       'MCupic\LogReport',
       'runLogReport'
);

