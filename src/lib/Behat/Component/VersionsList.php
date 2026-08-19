<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\Behat\Component;

use Behat\Mink\Session;
use Ibexa\Behat\Browser\Component\Component;
use Ibexa\Behat\Browser\Locator\XPathLocator;

/**
 * Reads the "Open drafts" table on the Versions tab (content/tab/versions/tab.html.twig).
 *
 * That section, headline included, is only rendered at all when the content item has at least
 * one draft (`show_drafts_table` in the template), so counting its rows doubles as the way to
 * prove no draft was left behind: zero drafts means the whole section, and this locator's match,
 * is simply absent from the page.
 */
final class VersionsList extends Component
{
    private const string DRAFTS_HEADLINE = 'Open drafts';

    public function __construct(
        readonly Session $session
    ) {
        parent::__construct($session);
    }

    public function getDraftVersionCount(): int
    {
        return $this->getHTMLPage()->setTimeout(0)->findAll($this->getLocator('draftVersionRow'))->count();
    }

    public function verifyIsLoaded(): void
    {
    }

    protected function specifyLocators(): array
    {
        return [
            new XPathLocator(
                'draftVersionRow',
                '//*[@id="ibexa-tab-location-view-versions"]' .
                '//div[@class="ibexa-table-header__headline" and normalize-space(text())="' . self::DRAFTS_HEADLINE . '"]' .
                '/following::table[1]//tbody/tr'
            ),
        ];
    }
}
