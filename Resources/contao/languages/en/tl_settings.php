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

/**
 * Legends
 */
$GLOBALS['TL_LANG']['tl_settings']['log_report_legend'] = "Log-Report";

/**
 * Fields
 */
$GLOBALS['TL_LANG']['tl_settings']['log_report_activate'] = array("Activate Log-Report");
$GLOBALS['TL_LANG']['tl_settings']['log_report_recipients'] = array("Email-Recipients:", "Please enter a comma-separated-list with email-adresses.");
$GLOBALS['TL_LANG']['tl_settings']['log_report_observed_tables'] = array("Observed tables:","Select the tables which you want to have been observed.");
$GLOBALS['TL_LANG']['tl_settings']['log_report_additional_observed_tables'] = array("Custom tables:","Add a comma-separated list containing tables, which you want have to be observed. (tl_table1,tl_table2,...)");
$GLOBALS['TL_LANG']['tl_settings']['log_report_send_email_when_db_changed'] = array("Only send email, when database has changed","If there are no changes in the database. The system will not send a message to you.");
$GLOBALS['TL_LANG']['tl_settings']['log_report_template'] = array("Template-settings", "Choose a personal template.");
