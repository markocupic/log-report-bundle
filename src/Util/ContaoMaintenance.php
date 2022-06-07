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

namespace Markocupic\LogReportBundle\Util;

use Doctrine\DBAL\Connection;

class ContaoMaintenance
{
    private Connection $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    /**
     * Purge tl_log_report.
     */
    public function purgeLogReportTable(): void
    {
        // This method is called from the maintenance module
        $this->connection->executeStatement('TRUNCATE TABLE tl_log_report');
    }
}
