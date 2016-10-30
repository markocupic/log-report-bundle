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
 * Add to palette
 */

$GLOBALS['TL_DCA']['tl_settings']['palettes']['default'] .= ';{log_report_legend:hide}, log_report_activate, log_report_observed_tables, log_report_additional_observed_tables, log_report_send_email_when_db_changed, log_report_recipients, log_report_template';


/**
 * Add fields
 */
$GLOBALS['TL_DCA']['tl_settings']['fields']['log_report_activate'] = array
(
		'label'	=>	&$GLOBALS['TL_LANG']['tl_settings']['log_report_activate'],
		'inputType'	=>	'checkbox',
		'default'	=>	'true',
		'eval'		=>	array('tl_class'=>'long clr')
);

$GLOBALS['TL_DCA']['tl_settings']['fields']['log_report_send_email_when_db_changed'] = array
(
		'label'	=>	&$GLOBALS['TL_LANG']['tl_settings']['log_report_send_email_when_db_changed'],
		'inputType'	=>	'checkbox',
		'default'	=>	'true',
		'eval'		=>	array('tl_class'=>'long clr')
);

$GLOBALS['TL_DCA']['tl_settings']['fields']['log_report_template'] = array
(
		'label'              => &$GLOBALS['TL_LANG']['tl_settings']['log_report_template'],
		'inputType'          => 'select',
		'options_callback'   => array('log_report_settings', 'getPartialTemplates'),
		'eval'               => array('tl_class'=>'long clr')
);

$GLOBALS['TL_DCA']['tl_settings']['fields']['log_report_recipients'] = array
(
		'label'       =>	&$GLOBALS['TL_LANG']['tl_settings']['log_report_recipients'],
		'inputType'   =>	'text',
		'eval'        =>	array('tl_class'=>'long clr')
);

$GLOBALS['TL_DCA']['tl_settings']['fields']['log_report_observed_tables'] = array
(
		'label'       =>	&$GLOBALS['TL_LANG']['tl_settings']['log_report_observed_tables'],
		'inputType'	=>	'checkbox',
		'options'	=>	array('tl_content','tl_member','tl_news','tl_user'),
		'eval'		=>	array('multiple'=>true, 'tl_class'=>'long clr')
);
$GLOBALS['TL_DCA']['tl_settings']['fields']['log_report_additional_observed_tables'] = array
(
		'label'       =>	&$GLOBALS['TL_LANG']['tl_settings']['log_report_additional_observed_tables'],
		'inputType'   =>	'text',
		'eval'		=>	array('tl_class'=>'long clr')
);


class log_report_settings extends \Backend
{

	/**
	 * Import the back end user object
	 */
	public function __construct()
	{
		parent::__construct();
		$this->import('BackendUser', 'User');
	}


	/**
	 * Return all navigation templates as array
	 * @param object
	 * @return array
	 */
	public function getPartialTemplates(DataContainer $dc)
	{
		$intPid = $dc->activeRecord->pid;
		return $this->getTemplateGroup('log_report_partial', $intPid);
	}
}
