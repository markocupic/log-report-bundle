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

namespace Markocupic\LogReportBundle\DataContainer;

use Contao\Backend;
use Contao\CoreBundle\Framework\Adapter;
use Contao\CoreBundle\Framework\ContaoFramework;
use Contao\CoreBundle\DependencyInjection\Attribute\AsCallback;
use Contao\DataContainer;

class Settings
{

    private Adapter $backend;

    public function __construct(
        private readonly ContaoFramework $framework,
    )
    {
        $this->backend = $this->framework->getAdapter(Backend::class);
    }

    #[AsCallback(table: 'tl_settings', target: 'fields.log_report_template.options', priority: 100)]
    public function getPartialTemplates(DataContainer $dc)
    {
        return $this->backend->getTemplateGroup('log_report_partial');
    }
}
