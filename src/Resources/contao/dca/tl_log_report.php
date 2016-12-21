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

$GLOBALS['TL_DCA']['tl_log_report'] = array(
       'config' => array(
              'dataContainer' => 'Table',
              'sql' => array(
                     'keys' => array(
                            'id' => 'primary'
                     )
              )
       ),

       'fields' => array(
              'id' => array(
                     'sql' => "int(10) unsigned NOT NULL auto_increment"
              ),
              'date' => array(
                     'sql' => "varchar(10) NOT NULL default ''"
              ),
              'recipients' => array(
                     'sql' => "text NOT NULL"
              ),
              'report' => array(
                     'sql' => "text NOT NULL"
              )
       )
);
 