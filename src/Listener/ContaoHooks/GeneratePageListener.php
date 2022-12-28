<?php

declare(strict_types=1);

namespace Markocupic\LogReportBundle\Listener\ContaoHooks;

use Contao\Config;
use Contao\CoreBundle\Framework\Adapter;
use Contao\CoreBundle\Framework\ContaoFramework;
use Contao\CoreBundle\ServiceAnnotation\Hook;
use Contao\Database;
use Contao\Email;
use Contao\Environment;
use Contao\File;
use Contao\FrontendTemplate;
use Contao\StringUtil;
use Contao\System;

/**
 * @Hook("generatePage")
 */
class GeneratePageListener
{
    // Set this to true when testing the plugin
    private const LOG_REPORT_TEST_MODE = false;

    private ContaoFramework $framework;
    private string $projectDir;
    private string $strTemplate = 'log_report';
    private string $strPartialTemplate = 'log_report_partial';
    private array|null $arrObservedTables = null;
    private int $countReports = 0;
    private array $arrReport = [];
    private string $dateKey;

    // Adapters
    private Adapter $config;
    private Adapter $database;
    private Adapter $environment;
    private Adapter $stringUtil;
    private Adapter $system;

    public function __construct(ContaoFramework $framework, string $projectDir)
    {
        $this->framework = $framework;
        $this->projectDir = $projectDir;

        // Adapters
        $this->config = $this->framework->getAdapter(Config::class);
        $this->database = $this->framework->getAdapter(Database::class);
        $this->environment = $this->framework->getAdapter(Environment::class);
        $this->stringUtil = $this->framework->getAdapter(StringUtil::class);
        $this->system = $this->framework->getAdapter(System::class);

        // Get the (custom) frontend template
        $this->strPartialTemplate = \strlen(trim($this->config->get('log_report_template'))) ? $this->config->get('log_report_template') : $this->strPartialTemplate;

        $this->dateKey = date('Y_m_d');
    }

    public function __invoke(): void
    {
        if (true !== $this->config->get('log_report_activate')) {
            return;
        }

        if (!$this->config->get('log_report_recipients')) {
            return;
        }

        if (!isset($GLOBALS['TL_CONFIG']['log_report_observed_tables'])) {
            return;
        }

        $arrObserved = $this->stringUtil->deserialize($GLOBALS['TL_CONFIG']['log_report_observed_tables'], true);
        $arrAdditional = explode(',', $GLOBALS['TL_CONFIG']['log_report_additional_observed_tables'] ?? '');

        // Clean array, remove duplicate or empty values
        $arrObservedTables = array_merge($arrObserved, $arrAdditional);
        $arrObservedTables = array_map('trim', $arrObservedTables);
        $arrObservedTables = array_filter($arrObservedTables);
        $arrObservedTables = array_values($arrObservedTables);
        $arrObservedTables = array_unique($arrObservedTables);

        if (empty($arrObservedTables)) {
            return;
        }

        $this->arrObservedTables = $arrObservedTables;

        $template = new FrontendTemplate($this->strTemplate);
        $template->arrObservedTables = $this->arrObservedTables;

        $template->loadLanguageFile('default');

        $objReport = $this->database
            ->getInstance()
            ->prepare('SELECT * FROM tl_log_report WHERE date = ?')
            ->execute($this->dateKey)
        ;

        // If a report was already sent today, stop here
        if (!$objReport->numRows || self::LOG_REPORT_TEST_MODE) {
            // Search for new Versions in the db
            $this->getNewVersions();

            // Inject the partial HTML to the main template
            $template->report = $this->arrReport;
            $this->arrReport = [];

            $htmlMailContent = $template->parse();

            // Send email
            if ($this->countReports < 1 && $GLOBALS['TL_CONFIG']['log_report_send_email_when_db_changed']) {
                // If there are no changes
                // and $GLOBALS['TL_CONFIG']['log_report_send_email_when_db_changed'] is activated
                // no email will be sent to the recipients.
            } else {
                $this->sendEmail($htmlMailContent);
            }

            // Insert new record
            $set = [
                'date' => $this->dateKey,
                'recipients' => $GLOBALS['TL_CONFIG']['log_report_recipients'],
                'report' => $htmlMailContent,
            ];

            // Store report in tl_log_report and in tl_log
            $objInsertStmt = $this->database
                ->getInstance()
                ->prepare('INSERT INTO tl_log_report %s')
                ->set($set)
                ->execute()
            ;

            if ($objInsertStmt->affectedRows) {
                $insertId = $objInsertStmt->insertId;
                $this->system->log('Log Report has been executed and an email was sent to the admin.', __CLASS__.' '.__FUNCTION__.'()', TL_GENERAL);
                $this->system->log(sprintf('A new version of tl_log_report ID %s has been created', $insertId), __CLASS__.' '.__FUNCTION__.'()', TL_GENERAL);
            }
        }
    }

    /**
     * Get log entries from tl_log.
     */
    private function getNewVersions(): void
    {
        $objLog = $this->database
            ->getInstance()
            ->prepare('SELECT * FROM tl_log WHERE log_report_date = ? ORDER BY tstamp')
            ->execute('')
        ;

        if (!$objLog->numRows) {
            return;
        }

        // Continue, if there are some unreported changes
        while ($objLog->next()) {
            // Create a html-table for each new row
            $this->createPartialHtml($objLog->text, $objLog->username, (int) $objLog->tstamp);

            // Update column log_report_date in tl_log with the current date
            $set = [
                'log_report_date' => $this->dateKey,
            ];

            $this->database
                ->getInstance()
                ->prepare('UPDATE tl_log %s WHERE id=?')
                ->set($set)
                ->execute($objLog->id)
            ;
        }
    }

    private function createPartialHtml(string $str, string $username, int $tstamp): void
    {
        foreach ($this->arrObservedTables as $table) {
            $table = trim($table);

            if ('' !== $table) {
                if (str_contains($str, $table)) {
                    $arr_patterns = [
                        // A new entry "tl_content.id=895" has been created (parent records: tl_article.id=211, tl_page.id=145)
                        'new_entry' => '/A new entry \&quot\;(?P<table>\w+).id=(?P<id>\d+)\&quot\; has been created/',
                        // A new version of record "tl_business.id=223" has been created (parent records: tl_user.id=911)
                        'edit' => '/of record \&quot\;(?P<table>\w+).id=(?P<id>\d+)\&quot\; has been created/',
                        // DELETE FROM tl_content WHERE id=896
                        'delete' => '/DELETE FROM (?P<table>\w+) WHERE id=(?P<id>\d+)/',
                    ];

                    foreach ($arr_patterns as $logType => $pattern) {
                        if (!preg_match($pattern, $str, $treffer)) {
                            continue;
                        }

                        $table = $treffer['table'];
                        $id = $treffer['id'];

                        if (\strlen($table) && \strlen($id)) {
                            ++$this->countReports;
                            $template = new \FrontendTemplate($this->strPartialTemplate);
                            $template->table = $table;
                            $template->logMessage = $str;
                            $template->type = strtoupper($logType);

                            $objDb = $this->database
                                ->getInstance()
                                ->prepare(sprintf('SELECT * FROM %s WHERE id=?', $table))
                                ->execute($id)
                            ;

                            $fields = [];

                            if ($objDb->numRows) {
                                // Create the backend link which links directly to the contao backend
                                if ('tl_content' === $table) {
                                    $backendUrl = $this->environment->get('base').sprintf('contao?do=%s&table=%s&act=edit&id=%s', 'article', $table, $id);
                                } elseif ('tl_news' === $table) {
                                    $backendUrl = $this->environment->get('base').sprintf('contao?do=%s&table=%s&act=edit&id=%s', str_replace('tl_', '', $table), $table, $id);
                                } elseif ('tl_calendar_events' === $table) {
                                    $backendUrl = $this->environment->get('base').sprintf('contao?do=%s&table=%s&id=%s&act=edit', 'calendar', 'tl_calendar_events', $id);
                                } else {
                                    $backendUrl = $this->environment->get('base').sprintf('contao?do=%s&act=edit&id=%s', str_replace('tl_', '', $table), $id);
                                }

                                $fields['backendUrl'] = '<a href="'.$backendUrl.'" title="go to contao backend">'.$GLOBALS['TL_LANG']['default']['linkToContaoBackendModule'].'</a>';

                                $arrFields = $this->database->getInstance()->listFields($table);

                                foreach ($arrFields as $arrField) {
                                    // For security reasons the password will not be displayed
                                    if ('password' === $arrField['name'] || 'PRIMARY' === $arrField['name']) {
                                        continue;
                                    }
                                    $fields[$arrField['name']] = $objDb->{$arrField['name']};
                                }
                            }

                            $template->username = $username;
                            $template->date = date('l, d. F Y, H:i', (int) $tstamp);
                            $template->fields = $fields;

                            // Only the latest versions will be sent by the email
                            if (!isset($this->arrReport[$table.'_html'])) {
                                $this->arrReport[$table.'_html'] = [];
                            }

                            $this->arrReport[$table.'_html'][$table.'_'.$id] = $template->parse();
                        }
                    }
                }
            }
        }
    }

    /**
     * @throws \Exception
     */
    private function sendEmail(string $htmlMessage = ''): void
    {
        $arr_recipients = explode(',', $this->config->get('log_report_recipients'));

        if (!\is_array($arr_recipients)) {
            return;
        }

        if (!\count($arr_recipients)) {
            return;
        }
        // Create the attachment-file
        $filepath = 'system/html/log_report_'.time().'.html';
        $file = new File($filepath);
        $file->write($htmlMessage);
        $file->close();

        foreach ($arr_recipients as $recipient) {
            $email = new Email();
            $email->charset = 'UTF-8';
            $email->priority = 'high';
            $email->from = $GLOBALS['TL_ADMIN_EMAIL'] ?? $this->config->get('adminEmail');
            $email->fromName = 'Log Report ('.$this->environment->get('host').')';
            $email->subject = 'change log contao';
            $email->text = 'A new log report was sent to you! Please open the email with a html-compatible email-program!';
            $email->html = $htmlMessage;
            $email->attachFile($this->projectDir.'/'.$filepath);

            if (\strlen(trim($recipient))) {
                $email->sendTo(trim($recipient));
            }
        }

        // Delete the tmp-file
        $file->delete();
    }
}
