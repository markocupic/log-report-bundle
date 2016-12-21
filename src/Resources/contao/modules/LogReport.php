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
 * Run in a custom namespace, so the class can be replaced
 */
namespace MCupic;

class LogReport extends \Frontend
{

    protected $strTemplate = 'log_report';
    protected $strPartialTemplate = 'log_report_partial';
    protected $countReports = 0;
    protected $token;


    public function __construct()
    {

        parent::__construct();
        //assigning the frontend template
        $this->strPartialTemplate = strlen(trim($GLOBALS['TL_CONFIG']['log_report_template'])) ? $GLOBALS['TL_CONFIG']['log_report_template'] : $this->strPartialTemplate;
        $this->token = sha1(\Encryption::hash(md5(microtime())) . sha1($GLOBALS['TL_CONFIG']['encryptionKey']));
    }


    /**
     * the module controller
     */
    public function runLogReport()
    {

        // nur zu Testzwecken
        define('LOG_REPORT_TEST_MODE', false);

        if (!isset($GLOBALS['TL_CONFIG']['log_report_activate']))
        {
            return;
        }

        if ($GLOBALS['TL_CONFIG']['log_report_activate'] !== true)
        {
            return;
        }


        if (trim($GLOBALS['TL_CONFIG']['log_report_recipients']) == '')
        {
            return;
        }

        $arrObservedTables = array_unique(array_merge(deserialize($GLOBALS['TL_CONFIG']['log_report_observed_tables'], true), explode(',', $GLOBALS['TL_CONFIG']['log_report_additional_observed_tables'])));
        if (!is_array($arrObservedTables))
        {
            return;
        }

        // Remove empty value with a callback function
        $this->arrObservedTables = array_filter($arrObservedTables, array(
            "MCupic\LogReport",
            "isNotEmpty",
        ));

        if (count($this->arrObservedTables) < 1)
        {
            return;
        }

        $template = new \FrontendTemplate($this->strTemplate);
        $template->arrObservedTables = $this->arrObservedTables;
        $template->loadLanguageFile('default');
        $this->dateKey = date("Y_m_d");
        // If a report was allready sent today, abort here
        $objReport = $this->Database->prepare("SELECT * FROM tl_log_report WHERE date=?")->execute($this->dateKey);
        if ($objReport->numRows == 0 || LOG_REPORT_TEST_MODE === true)
        {
            // Search for new Versions in the db
            $this->getNewVersions();

            // Add the partialHtml to the main-template
            $template->report = $GLOBALS['LOG_REPORT'];
            unset($GLOBALS['LOG_REPORT']);
            $htmlMailContent = $template->parse();

            // Send email
            if ($this->countReports < 1 && true == $GLOBALS['TL_CONFIG']['log_report_send_email_when_db_changed'])
            {
                // When there are noch changes
                // and $GLOBALS['TL_CONFIG']['log_report_send_email_when_db_changed'] is activated
                // no email will be sent to the recipients.
            }
            else
            {
                $this->sendEmail($htmlMailContent);
            }

            // db insert
            $set = array(
                "date" => $this->dateKey,
                "recipients" => $GLOBALS['TL_CONFIG']['log_report_recipients'],
                "report" => $htmlMailContent,
            );

            // Store report in tl_log_report and in tl_log
            $objInsertStmt = $this->Database->prepare("INSERT INTO tl_log_report %s")->set($set)->execute();
            if ($objInsertStmt->affectedRows)
            {
                $insertId = $objInsertStmt->insertId;
                $this->log('LogReport has been executed and an email was sent to the admin.', __CLASS__ . ' ' . __FUNCTION__ . '()', TL_GENERAL);
                $this->log(sprintf('A new version of tl_log_report ID %s has been created', $insertId), __CLASS__ . ' ' . __FUNCTION__ . '()', TL_GENERAL);
            }
        }
    }


    /**
     * get log entries in tl_log
     */
    private function getNewVersions()
    {

        $objLog = $this->Database->prepare("SELECT * FROM tl_log WHERE log_report_date=? ORDER BY tstamp")->execute("");
        if ($objLog->numRows < 1)
        {
            return;
        }

        // Continue, if there are some unreported changes
        while ($objLog->next())
        {
            // Create a html-table for each new row
            $this->createPartialHtml($objLog->text, $objLog->username, $objLog->tstamp);
            // Update column log_report_date in tl_log with the current date
            $set = array(
                "log_report_date" => $this->dateKey,
            );
            $objUpdate = $this->Database->prepare("UPDATE tl_log %s WHERE id=?")->set($set)->execute($objLog->id);
        }
    }


    /**
     * @param string $str
     * @param string $username
     * @param integer $tstamp
     */
    private function createPartialHtml($str, $username, $tstamp)
    {

        $this->import('Environment');

        foreach ($this->arrObservedTables as $table)
        {
            $table = trim($table);
            if ($table != "")
            {
                if (false !== strpos($str, $table))
                {
                    $arr_patterns = array(
                        // A new entry "tl_content.id=895" has been created (parent records: tl_article.id=211, tl_page.id=145)
                        'new_entry' => '/A new entry \&quot\;(?P<table>\w+).id=(?P<id>\d+)\&quot\; has been created/',
                        // A new version of record "tl_business.id=223" has been created (parent records: tl_user.id=911)
                        'edit' => '/of record \&quot\;(?P<table>\w+).id=(?P<id>\d+)\&quot\; has been created/',
                        // DELETE FROM tl_content WHERE id=896
                        'delete' => '/DELETE FROM (?P<table>\w+) WHERE id=(?P<id>\d+)/',
                    );

                    foreach ($arr_patterns as $logType => $pattern)
                    {
                        // ab php 5.2.2 named subpatterns
                        preg_match($pattern, $str, $treffer);
                        $table = $treffer["table"];
                        $id = $treffer["id"];
                        if (strlen($table) && strlen($id))
                        {
                            $this->countReports++;
                            $template = new \FrontendTemplate($this->strPartialTemplate);
                            $template->table = $table;
                            $template->logMessage = $str;
                            $template->type = strtoupper($logType);
                            $objDb = $this->Database->prepare(sprintf("SELECT * FROM %s WHERE id=?", $table))->execute($id);
                            $arrFields = $this->Database->listFields($table);
                            $fields = array();
                            if ($objDb->numRows)
                            {

                                // Create the backend-link which links directly to the contao-backend
                                if ($table == "tl_content")
                                {
                                    $backendUrl = $this->Environment->base . sprintf("contao?&do=%s&table=%s&act=edit&id=%s", "article", $table, $id);
                                }
                                elseif ($table == "tl_news")
                                {
                                    $backendUrl = $this->Environment->base . sprintf("contao?do=%s&table=%s&act=edit&id=%s", str_replace("tl_", "", $table), $table, $id);
                                }
                                elseif ($table == "tl_calendar_events")
                                {
                                    $backendUrl = $this->Environment->base . sprintf("contao?do=%s&table=%s&id=%s&act=edit", 'calendar', 'tl_calendar_events', $id);
                                }
                                else
                                {
                                    $backendUrl = $this->Environment->base . sprintf("contao?do=%s&act=edit&id=%s", str_replace("tl_", "", $table), $id);
                                }

                                $fields["backendUrl"] = '<a href="' . $backendUrl . '" title="go to contao backend">' . $GLOBALS['TL_LANG']['default']['linkToContaoBackendModule'] . '</a>';
                                foreach ($arrFields as $arrField)
                                {
                                    // For security reasons the password will not be displayed
                                    if ($arrField["name"] == "password" || $arrField["name"] == "PRIMARY")
                                    {
                                        continue;
                                    }
                                    $fields[$arrField["name"]] = $objDb->{$arrField["name"]};
                                }
                            }
                            $template->username = $username;
                            $template->date = \Date::parse('l, d. F Y, H:i', $tstamp);
                            $template->fields = $fields;
                            // Only the latest versions will be sent by the email
                            $GLOBALS['LOG_REPORT'][$table . "_html"][$table . "_" . $id] = $template->parse();
                        }
                    }
                }
            }
        }
    }


    /**
     * purge tl_log_report
     */
    public function purgeLogReportTable()
    {

        // This method is called from the maintenance module
        $this->Database->execute("TRUNCATE TABLE tl_log_report");
    }


    /**
     * @param string $var
     * @return string $var
     */
    public function isNotEmpty($var)
    {

        if (strlen($var))
        {
            return $var;
        }
    }


    /**
     * @param string $htmlMessage
     */
    private function sendEmail($htmlMessage = "")
    {

        $arr_recipients = explode(',', $GLOBALS['TL_CONFIG']['log_report_recipients']);
        if (!is_array($arr_recipients))
        {
            return;
        }
        if (!count($arr_recipients))
        {
            return;
        }
        // Create the attachment-file
        $filepath = "system/html/log_report_" . time() . ".html";
        $file = new \File($filepath);
        $file->write($htmlMessage);
        $file->close();
        foreach ($arr_recipients as $recipient)
        {
            $email = new \Email();
            $email->charset = 'UTF-8';
            $email->priority = 'high';
            $email->from = $GLOBALS['TL_ADMIN_EMAIL'];
            $email->fromName = 'Log Report (' . \Environment::get('host') . ')';
            $email->subject = "change log contao";
            $email->text = 'A new log report was sent to you! Please open te email with a html-compatible email-programm!';
            $email->html = $htmlMessage;
            $email->attachFile(TL_ROOT . '/' . $filepath);
            if (strlen(trim($recipient)))
            {
                $email->sendTo(trim($recipient));
            }
        }
        // Delete the tmp-file
        $file->delete();
    }
}
